<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use Http\Mock\Client;
use neto737\BitGoSDK\BitGo;
use neto737\BitGoSDK\Config;
use neto737\BitGoSDK\Internal\Http;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestInterface;

/**
 * Sends every request to a mock PSR-18 client, so tests never touch the network. Without a queued response, the mock
 * answers 200 with an empty body.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected const API = 'https://app.bitgo-test.com/api/v2/';
    protected const TOKEN = 'test-token';

    protected Client $client;
    protected Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
        $this->client = new Client($this->factory);
    }

    protected function bitgo(): BitGo
    {
        return new BitGo(Config::testnet(self::TOKEN), $this->client, $this->factory, $this->factory);
    }

    protected function http(): Http
    {
        return new Http(Config::testnet(self::TOKEN), $this->client, $this->factory, $this->factory);
    }

    /**
     * Queue a response. An array is sent as JSON, a string as it is.
     *
     * @param array<mixed>|string $body
     */
    protected function respond(array|string $body = [], int $status = 200): void
    {
        $this->client->addResponse($this->factory->createResponse($status)->withBody(
            $this->factory->createStream(is_string($body) ? $body : json_encode((object) $body, JSON_THROW_ON_ERROR))
        ));
    }

    /**
     * @return list<RequestInterface>
     */
    protected function requests(): array
    {
        return array_values($this->client->getRequests());
    }

    protected function lastRequest(): RequestInterface
    {
        $requests = $this->requests();
        self::assertNotEmpty($requests, 'No request was sent.');
        return $requests[count($requests) - 1];
    }

    /**
     * The request body, decoded from JSON, or null if there was none.
     */
    protected static function body(RequestInterface $request): mixed
    {
        $contents = (string) $request->getBody();
        return $contents === '' ? null : json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Assert one request's method, URL and decoded body.
     *
     * @param array<string, mixed>|null $body
     */
    protected static function assertRequest(RequestInterface $request, string $method, string $url, ?array $body): void
    {
        self::assertSame($method, $request->getMethod());
        self::assertSame($url, (string) $request->getUri());
        self::assertSame($body, self::body($request));
    }
}
