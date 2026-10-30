<?php

/**
 *  ____  _ _    ____      ____  ____  _  __
 * | __ )(_) |_ / ___| ___/ ___||  _ \| |/ /
 * |  _ \| | __| |  _ / _ \___ \| | | | ' /
 * | |_) | | |_| |_| | (_) |__) | |_| | . \
 * |____/|_|\__|\____|\___/____/|____/|_|\_\
 *
 * @package BitGoSDK PHP
 * @author  Neto Melo <neto737@live.com>
 * @license https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace neto737\BitGoSDK\Internal;

use neto737\BitGoSDK\Amount;
use neto737\BitGoSDK\BitGo;
use neto737\BitGoSDK\Config;
use neto737\BitGoSDK\Exception\ApiException;
use neto737\BitGoSDK\Exception\BitGoException;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use neto737\BitGoSDK\Exception\TransportException;
use neto737\BitGoSDK\Exception\UnexpectedResponseException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Sends requests to the BitGo API and decodes the responses.
 *
 * Path params fill the {name} placeholders of a path template. Params passed by name win over $extra, and null params
 * are never sent, so a method's defaults leave BitGo's defaults in place.
 *
 * GET, PUT and DELETE requests are retried as the config's RetryPolicy says. POST requests are sent once.
 *
 * @internal Not part of the public API. It may change or go away in any release.
 */
final class Http
{
    // Sending one of these twice has the same effect as sending it once, so only these are retried.
    private const IDEMPOTENT_METHODS = ['GET', 'PUT', 'DELETE'];

    private ?ResponseInterface $lastResponse = null;

    /** @var \Closure(int): void */
    private readonly \Closure $sleep;

    /**
     * @param (\Closure(int): void)|null $sleep    Waits the given milliseconds between attempts. Tests pass their own.
     */
    public function __construct(
        private readonly Config $config,
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        ?\Closure $sleep = null,
    ) {
        $this->sleep = $sleep ?? static function (int $ms): void {
            usleep($ms * 1000);
        };
    }

    /**
     * The response to the latest request sent, or null before the first one and after one that got no response.
     */
    public function lastResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }

    /**
     * @param array<string, string> $path
     * @param array<string, mixed> $query
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     * @throws BitGoException
     */
    public function get(string $template, array $path, array $query = [], array $extra = []): array
    {
        return self::decode($this->send('GET', $template, $path, self::merge($query, $extra), null));
    }

    /**
     * One batch of a list endpoint.
     *
     * @param array<string, string> $path
     * @param array<string, mixed> $query
     * @param array<string, mixed> $extra
     * @param string $itemsKey    The key that holds the items, for example 'addresses'
     * @return array{items: list<array<string, mixed>>, nextBatchPrevId: ?string}
     * @throws BitGoException
     */
    public function getBatch(string $template, array $path, array $query, array $extra, string $itemsKey): array
    {
        $response = $this->send('GET', $template, $path, self::merge($query, $extra), null);
        $batch = self::decode($response);
        $invalid = static fn () => new UnexpectedResponseException('BitGo returned a batch whose "' . $itemsKey . '" isn\'t a list of objects.', $response, $batch);
        $raw = $batch[$itemsKey] ?? [];
        if (!is_array($raw) || !array_is_list($raw)) {
            throw $invalid();
        }
        $items = [];
        foreach ($raw as $item) {
            if (!self::isObject($item)) {
                throw $invalid();
            }
            $items[] = $item;
        }
        $next = $batch['nextBatchPrevId'] ?? null;

        return ['items' => $items, 'nextBatchPrevId' => is_string($next) && $next !== '' ? $next : null];
    }

    /**
     * @param array<string, string> $path
     * @param array<string, mixed> $body
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     * @throws BitGoException
     */
    public function post(string $template, array $path, array $body = [], array $extra = []): array
    {
        return self::decode($this->send('POST', $template, $path, [], self::merge($body, $extra)));
    }

    /**
     * @param array<string, string> $path
     * @param array<string, mixed> $body
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     * @throws BitGoException
     */
    public function put(string $template, array $path, array $body = [], array $extra = []): array
    {
        return self::decode($this->send('PUT', $template, $path, [], self::merge($body, $extra)));
    }

    /**
     * A DELETE with a JSON body, which is how BitGo's remove endpoints, such as removing a webhook, take their params.
     *
     * @param array<string, string> $path
     * @param array<string, mixed> $body
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     * @throws BitGoException
     */
    public function delete(string $template, array $path, array $body = [], array $extra = []): array
    {
        return self::decode($this->send('DELETE', $template, $path, [], self::merge($body, $extra)));
    }

    /**
     * @param array<string, string> $path
     * @param array<string, mixed> $query
     * @param array<string, mixed>|null $body    null sends no body at all
     * @throws InvalidArgumentException|TransportException
     */
    private function send(string $method, string $template, array $path, array $query, ?array $body): ResponseInterface
    {
        $url = $this->config->baseUrl . Path::build($template, $path);
        if ($query !== []) {
            $url .= '?' . self::buildQuery($query);
        }
        $json = $body === null ? null : self::encode($body);
        $policy = $this->config->retryPolicy;
        $idempotent = in_array($method, self::IDEMPOTENT_METHODS, true);

        for ($attempt = 1; ; $attempt++) {
            $canRetry = $idempotent && $attempt < $policy->maxAttempts;
            $this->lastResponse = null;
            try {
                $response = $this->client->sendRequest($this->request($method, $url, $json));
            } catch (ClientExceptionInterface $e) {
                if (!$canRetry) {
                    throw new TransportException($e);
                }
                ($this->sleep)($policy->backoffMs($attempt));
                continue;
            }

            $this->lastResponse = $response;
            $status = $response->getStatusCode();
            $delay = $canRetry && ($status === 429 || $status >= 500) ? $policy->delayMs($attempt, $response->getHeaderLine('Retry-After')) : null;
            if ($delay === null) {
                return $response;
            }
            ($this->sleep)($delay);
        }
    }

    /**
     * A new request for each attempt, since sending one can consume its body stream.
     */
    private function request(string $method, string $url, ?string $json): RequestInterface
    {
        $request = $this->requestFactory->createRequest($method, $url)
            ->withHeader('Authorization', 'Bearer ' . $this->config->accessToken)
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', 'BitGoSDK-PHP/' . BitGo::VERSION);
        if ($json !== null) {
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream($json));
        }
        return $request;
    }

    /**
     * Params passed by name, on top of $extra, without nulls.
     *
     * @param array<string, mixed> $params
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private static function merge(array $params, array $extra): array
    {
        $notNull = static fn ($value) => $value !== null;
        return array_merge(array_filter($extra, $notNull), array_filter($params, $notNull));
    }

    /**
     * A query string that sends booleans as "true"/"false" instead of http_build_query's 1/0, and an Amount as its
     * base units instead of its properties.
     *
     * @param array<string, mixed> $params
     */
    private static function buildQuery(array $params): string
    {
        return http_build_query(array_map(static fn ($value) => match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            $value instanceof Amount => $value->baseUnits,
            default => $value,
        }, $params));
    }

    /**
     * @param array<string, mixed> $body
     * @throws InvalidArgumentException if the body can't be encoded as JSON, for example a string that isn't UTF-8
     */
    private static function encode(array $body): string
    {
        try {
            // Cast to an object so an empty body is sent as {} rather than [].
            return json_encode((object) $body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException('The request body can\'t be encoded as JSON: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @return array<string, mixed>
     * @throws ApiException for a non-2xx status, or a 2xx whose body isn't a JSON object
     */
    private static function decode(ResponseInterface $response): array
    {
        $contents = (string) $response->getBody();
        try {
            $data = $contents === '' ? [] : json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $data = null;
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw ApiException::fromResponse($response, self::isObject($data) ? $data : []);
        }
        if (!self::isObject($data)) {
            throw new UnexpectedResponseException('BitGo returned HTTP ' . $status . ' with a body that isn\'t a JSON object.', $response, []);
        }
        return $data;
    }

    /**
     * Whether $data is a decoded JSON object ({} decodes to [] too).
     *
     * @phpstan-assert-if-true array<string, mixed> $data
     */
    private static function isObject(mixed $data): bool
    {
        return is_array($data) && ($data === [] || !array_is_list($data));
    }
}
