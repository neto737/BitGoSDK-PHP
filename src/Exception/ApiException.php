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

namespace neto737\BitGoSDK\Exception;

use Psr\Http\Message\ResponseInterface;

/**
 * BitGo answered with an error status. Subclasses cover the statuses you are most likely to handle.
 */
class ApiException extends \RuntimeException implements BitGoException
{
    /**
     * @param array<string, mixed> $body    The decoded error body, or [] if it wasn't JSON
     */
    final public function __construct(string $message, private readonly ResponseInterface $response, private readonly array $body)
    {
        parent::__construct($message, $response->getStatusCode());
    }

    /**
     * The exception for an error response, picking the subclass that matches its status.
     *
     * @internal
     * @param array<string, mixed> $body
     */
    public static function fromResponse(ResponseInterface $response, array $body): self
    {
        $status = $response->getStatusCode();
        $class = match (true) {
            $status === 401, $status === 403 => AuthenticationException::class,
            $status === 404 => NotFoundException::class,
            $status === 429 => RateLimitException::class,
            $status >= 500 => ServerException::class,
            default => self::class,
        };
        $error = $body['error'] ?? $body['message'] ?? null;
        $detail = is_string($error) && $error !== '' ? $error : $response->getReasonPhrase();

        return new $class(rtrim('BitGo returned HTTP ' . $status . ': ' . $detail, ': '), $response, $body);
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * BitGo's name for the error, for example 'UnauthorizedError', if it sent one.
     */
    public function getErrorName(): ?string
    {
        return is_string($this->body['name'] ?? null) ? $this->body['name'] : null;
    }

    /**
     * BitGo's ID for the failed request, if it sent one. Quote it when you contact BitGo support.
     */
    public function getRequestId(): ?string
    {
        return is_string($this->body['requestId'] ?? null) ? $this->body['requestId'] : null;
    }

    /**
     * @return array<string, mixed> The decoded error body, or [] if it wasn't JSON
     */
    public function getBody(): array
    {
        return $this->body;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
