<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Tests\Fixtures\RecordingTransport;
use PHPUnit\Framework\TestCase;

/**
 * B5: transport failures, non-2xx statuses and invalid JSON come back as ['error' => …, 'status' => …].
 */
final class ErrorHandlingTest extends TestCase
{
    public function testSdkReturnsErrorWhenRequestFails(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $sdk->walletId = 'w1';
        RecordingTransport::on($sdk)->fail(CURLE_COULDNT_CONNECT, 'Failed to connect to app.bitgo-test.com');

        self::assertSame(['error' => 'Failed to connect to app.bitgo-test.com', 'status' => 0], $sdk->getWallet());
    }

    public function testErrorWithoutMessageNamesTheCurlErrno(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $sdk->walletId = 'w1';
        RecordingTransport::on($sdk)->fail(CURLE_OPERATION_TIMEDOUT);

        self::assertSame(['error' => 'cURL error ' . CURLE_OPERATION_TIMEDOUT, 'status' => 0], $sdk->getWallet());
    }

    public function testExpressReturnsErrorArrayWhenRequestFails(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        RecordingTransport::on($express)->fail(CURLE_COULDNT_CONNECT, 'Connection refused');

        self::assertSame(['error' => 'Connection refused', 'status' => 0], $express->pingExpress());
    }

    public function testExpressReturnsErrorObjectWhenObjectRequestFails(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $express->walletId = 'w1';
        RecordingTransport::on($express)->fail(CURLE_COULDNT_CONNECT, 'Connection refused');

        self::assertEquals((object) ['error' => 'Connection refused', 'status' => 0], $express->buildTransaction([['address' => 'a', 'amount' => 1]]));
    }

    public function testSuccessfulResponseIsReturnedUnchanged(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $sdk->walletId = 'w1';
        RecordingTransport::on($sdk)->respond('{"id":"w1"}')->respond('', 204);

        self::assertSame(['id' => 'w1'], $sdk->getWallet());
        self::assertNull($sdk->getWallet());
    }

    public function testBitGoErrorKeepsItsFieldsAndGainsStatus(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $sdk->walletId = 'w1';
        RecordingTransport::on($sdk)->respond('{"error":"wallet not found","name":"WalletNotFound"}', 404);

        self::assertSame(['error' => 'wallet not found', 'name' => 'WalletNotFound', 'status' => 404], $sdk->getWallet());
    }

    public function testNonJsonErrorReportsStatus(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $sdk->walletId = 'w1';
        RecordingTransport::on($sdk)->respond('<html>Bad Gateway</html>', 502);

        self::assertSame(['error' => 'HTTP 502', 'status' => 502], $sdk->getWallet());
    }

    public function testInvalidJsonOnSuccessIsAnError(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $sdk->walletId = 'w1';
        RecordingTransport::on($sdk)->respond('{"id":', 200);

        $result = $sdk->getWallet();

        self::assertStringStartsWith('Invalid JSON response: ', $result['error']);
        self::assertSame(200, $result['status']);
    }

    public function testExpressObjectErrorKeepsItsFields(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $express->walletId = 'w1';
        RecordingTransport::on($express)->respond('{"error":"insufficient funds","name":"Error"}', 400);

        self::assertEquals((object) ['error' => 'insufficient funds', 'name' => 'Error', 'status' => 400], $express->buildTransaction([]));
    }

    /**
     * @return array<string, array{BitGoSDK|BitGoExpress, callable(mixed): mixed, int, int}>
     */
    public static function clients(): array
    {
        return [
            'BitGoSDK' => [
                new BitGoSDK('token', 'tbtc', true),
                static fn (BitGoSDK $c) => $c->getTotalBalances(),
                BitGoSDK::DEFAULT_TIMEOUT,
                BitGoSDK::DEFAULT_CONNECT_TIMEOUT,
            ],
            'BitGoExpress' => [
                new BitGoExpress('localhost', 3080, 'tbtc'),
                static fn (BitGoExpress $c) => $c->pingExpress(),
                BitGoExpress::DEFAULT_TIMEOUT,
                BitGoExpress::DEFAULT_CONNECT_TIMEOUT,
            ],
        ];
    }

    /**
     * @dataProvider clients
     * @param BitGoSDK|BitGoExpress $client
     * @param callable(mixed): mixed $request
     */
    public function testTimeoutsDefaultAndCanBeChanged($client, callable $request, int $timeout, int $connectTimeout): void
    {
        $transport = RecordingTransport::on($client);

        $request($client);
        $client->setTimeout(5);
        $client->setConnectTimeout(2);
        $request($client);

        self::assertSame($timeout, $transport->requests[0]->options[CURLOPT_TIMEOUT]);
        self::assertSame($connectTimeout, $transport->requests[0]->options[CURLOPT_CONNECTTIMEOUT]);
        self::assertSame(5, $transport->requests[1]->options[CURLOPT_TIMEOUT]);
        self::assertSame(2, $transport->requests[1]->options[CURLOPT_CONNECTTIMEOUT]);
    }

    public function testNegativeTimeoutIsRejected(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');

        $this->expectException(\InvalidArgumentException::class);
        $express->setTimeout(-1);
    }

    public function testNegativeConnectTimeoutIsRejected(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);

        $this->expectException(\InvalidArgumentException::class);
        $sdk->setConnectTimeout(-1);
    }
}
