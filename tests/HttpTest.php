<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use Http\Client\Exception\NetworkException;
use neto737\BitGoSDK\BitGo;
use neto737\BitGoSDK\Exception\ApiException;
use neto737\BitGoSDK\Exception\AuthenticationException;
use neto737\BitGoSDK\Exception\BitGoException;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use neto737\BitGoSDK\Exception\NotFoundException;
use neto737\BitGoSDK\Exception\RateLimitException;
use neto737\BitGoSDK\Exception\ServerException;
use neto737\BitGoSDK\Exception\TransportException;
use neto737\BitGoSDK\Exception\UnexpectedResponseException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * What goes over the wire, and how responses and errors come back.
 */
final class HttpTest extends TestCase
{
    private const array PATH = ['coin' => 'tbtc', 'walletId' => 'w1'];

    public function testHeaders(): void
    {
        $this->http()->post('{coin}/wallet/{walletId}', self::PATH);

        $request = $this->lastRequest();
        self::assertSame('Bearer ' . self::TOKEN, $request->getHeaderLine('Authorization'));
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame('BitGoSDK-PHP/' . BitGo::VERSION, $request->getHeaderLine('User-Agent'));
    }

    public function testGetSendsNoBody(): void
    {
        $this->http()->get('{coin}/wallet/{walletId}', self::PATH);

        $request = $this->lastRequest();
        self::assertRequest($request, 'GET', self::API . 'tbtc/wallet/w1', null);
        self::assertFalse($request->hasHeader('Content-Type'));
    }

    public function testEmptyBodyIsAnObject(): void
    {
        $this->http()->post('{coin}/wallet/{walletId}', self::PATH);

        self::assertSame('{}', (string) $this->lastRequest()->getBody());
    }

    public function testOnlyNullParamsAreLeftOut(): void
    {
        $this->http()->post('{coin}/wallet/{walletId}', self::PATH, ['a' => null, 'zero' => 0, 'no' => false, 'empty' => '', 'url' => 'https://x/y']);

        self::assertSame('{"zero":0,"no":false,"empty":"","url":"https://x/y"}', (string) $this->lastRequest()->getBody());
    }

    public function testQueryString(): void
    {
        $this->http()->get('{coin}/wallet/{walletId}', self::PATH, ['skip' => null, 'yes' => true, 'no' => false, 'zero' => 0, 'chains' => [0, 10]]);

        self::assertSame(
            self::API . 'tbtc/wallet/w1?yes=true&no=false&zero=0&chains%5B0%5D=0&chains%5B1%5D=10',
            (string) $this->lastRequest()->getUri()
        );
    }

    public function testNamedParamsWinOverExtra(): void
    {
        $this->http()->post('{coin}/wallet/{walletId}', self::PATH, ['label' => 'named', 'chain' => null], ['label' => 'extra', 'chain' => 20, 'new' => 1, 'gone' => null]);

        self::assertSame(['label' => 'named', 'chain' => 20, 'new' => 1], self::body($this->lastRequest()));
    }

    public function testBodyThatIsNotUtf8IsRejectedBeforeSending(): void
    {
        $this->expectException(InvalidArgumentException::class);
        try {
            $this->http()->post('{coin}/wallet/{walletId}', self::PATH, ['label' => "\xB1\x31"]);
        } finally {
            self::assertSame([], $this->requests());
        }
    }

    /**
     * @return array<string, array{int, string, array<string, mixed>}>
     */
    public static function successes(): array
    {
        return [
            '200 with an object' => [200, '{"id":"w1"}', ['id' => 'w1']],
            'HTTP 201' => [201, '{"id":"w1"}', ['id' => 'w1']],
            'HTTP 202' => [202, '{"id":"w1"}', ['id' => 'w1']],
            '204 with no body' => [204, '', []],
            'empty object' => [200, '{}', []],
        ];
    }

    /**
     * @param array<string, mixed> $expected
     */
    #[DataProvider('successes')]
    public function testEverySuccessStatusIsDecoded(int $status, string $body, array $expected): void
    {
        $this->respond($body, $status);

        self::assertSame($expected, $this->http()->get('{coin}/wallet/{walletId}', self::PATH));
    }

    /**
     * @return array<string, array{int, class-string<ApiException>}>
     */
    public static function errors(): array
    {
        return [
            'HTTP 400' => [400, ApiException::class],
            'HTTP 401' => [401, AuthenticationException::class],
            'HTTP 403' => [403, AuthenticationException::class],
            'HTTP 404' => [404, NotFoundException::class],
            'HTTP 409' => [409, ApiException::class],
            'HTTP 429' => [429, RateLimitException::class],
            'HTTP 500' => [500, ServerException::class],
            'HTTP 503' => [503, ServerException::class],
        ];
    }

    /**
     * @param class-string<ApiException> $class
     */
    #[DataProvider('errors')]
    public function testErrorStatusesThrow(int $status, string $class): void
    {
        $this->respond(['error' => 'wallet not found', 'name' => 'WalletNotFound', 'requestId' => 'req-1'], $status);

        try {
            $this->http()->get('{coin}/wallet/{walletId}', self::PATH);
            self::fail('No exception was thrown.');
        } catch (ApiException $e) {
            self::assertSame($class, $e::class);
            self::assertInstanceOf(BitGoException::class, $e);
            self::assertSame('BitGo returned HTTP ' . $status . ': wallet not found', $e->getMessage());
            self::assertSame($status, $e->getStatusCode());
            self::assertSame($status, $e->getCode());
            self::assertSame('WalletNotFound', $e->getErrorName());
            self::assertSame('req-1', $e->getRequestId());
            self::assertSame(['error' => 'wallet not found', 'name' => 'WalletNotFound', 'requestId' => 'req-1'], $e->getBody());
            self::assertSame($status, $e->getResponse()->getStatusCode());
        }
    }

    public function testErrorWithoutAJsonBodyUsesTheReasonPhrase(): void
    {
        $this->respond('<html>Bad Gateway</html>', 502);

        try {
            $this->http()->get('{coin}/wallet/{walletId}', self::PATH);
            self::fail('No exception was thrown.');
        } catch (ServerException $e) {
            self::assertSame('BitGo returned HTTP 502: Bad Gateway', $e->getMessage());
            self::assertSame([], $e->getBody());
            self::assertNull($e->getErrorName());
            self::assertNull($e->getRequestId());
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unexpectedBodies(): array
    {
        return [
            'not JSON' => ['<html>OK</html>'],
            'a JSON list' => ['[1,2]'],
            'a JSON string' => ['"ok"'],
        ];
    }

    #[DataProvider('unexpectedBodies')]
    public function testSuccessWithABodyThatIsNotAnObjectThrows(string $body): void
    {
        $this->respond($body);

        $this->expectException(UnexpectedResponseException::class);
        $this->http()->get('{coin}/wallet/{walletId}', self::PATH);
    }

    public function testNetworkErrorThrowsTransportException(): void
    {
        $cause = new NetworkException('Connection refused', $this->factory->createRequest('GET', self::API));
        $this->client->addException($cause);

        try {
            $this->http()->get('{coin}/wallet/{walletId}', self::PATH);
            self::fail('No exception was thrown.');
        } catch (TransportException $e) {
            self::assertSame('The request to BitGo failed: Connection refused', $e->getMessage());
            self::assertSame($cause, $e->getPrevious());
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function badBatches(): array
    {
        return [
            'items not a list' => ['{"addresses":{"a":{"id":"1"}}}'],
            'item not an object' => ['{"addresses":[{"id":"1"},"2"]}'],
            'items a string' => ['{"addresses":"none"}'],
        ];
    }

    #[DataProvider('badBatches')]
    public function testBatchWhoseItemsAreNotAListOfObjectsThrows(string $body): void
    {
        $this->respond($body);

        $this->expectException(UnexpectedResponseException::class);
        $this->http()->getBatch('{coin}/wallet/{walletId}/addresses', self::PATH, [], [], 'addresses');
    }
}
