<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;

/**
 * B5: transport failures, non-2xx statuses and invalid JSON come back as ['error' => …, 'status' => …].
 */
final class ErrorHandlingTest extends TestCase
{
    use AccessesPrivateMembers;

    public function testSdkReturnsErrorWhenRequestFails(): void
    {
        $sdk = @new BitGoSDK('token', 'tbtc', true);
        $this->setPrivate($sdk, 'APIEndpoint', 'http://127.0.0.1:1/api/v2/tbtc');

        $result = $sdk->getWallet();

        self::assertIsString($result['error']);
        self::assertNotSame('', $result['error']);
        self::assertSame(0, $result['status']);
    }

    public function testExpressReturnsErrorObjectWhenObjectRequestFails(): void
    {
        $express = @new BitGoExpress('127.0.0.1', 1, 'tbtc');

        $result = $express->buildTransaction([['address' => 'a', 'amount' => 1]]);

        self::assertInstanceOf(\stdClass::class, $result);
        self::assertIsString($result->error);
        self::assertSame(0, $result->status);
    }

    public function testSuccessfulResponseIsReturnedUnchanged(): void
    {
        self::assertSame(['id' => 'w1'], $this->invoke(BitGoSDK::class, 'decodeResponse', '{"id":"w1"}', 200));
        self::assertNull($this->invoke(BitGoSDK::class, 'decodeResponse', '', 204));
    }

    public function testBitGoErrorKeepsItsFieldsAndGainsStatus(): void
    {
        self::assertSame(
            ['error' => 'wallet not found', 'name' => 'WalletNotFound', 'status' => 404],
            $this->invoke(BitGoSDK::class, 'decodeResponse', '{"error":"wallet not found","name":"WalletNotFound"}', 404)
        );
    }

    public function testNonJsonErrorReportsStatus(): void
    {
        self::assertSame(
            ['error' => 'HTTP 502', 'status' => 502],
            $this->invoke(BitGoSDK::class, 'decodeResponse', '<html>Bad Gateway</html>', 502)
        );
    }

    public function testInvalidJsonOnSuccessIsAnError(): void
    {
        $result = $this->invoke(BitGoSDK::class, 'decodeResponse', '{"id":', 200);

        self::assertIsArray($result);
        self::assertStringStartsWith('Invalid JSON response: ', $result['error']);
        self::assertSame(200, $result['status']);
    }

    public function testExpressObjectErrorKeepsItsFields(): void
    {
        $result = $this->invoke(BitGoExpress::class, 'decodeResponse', '{"error":"insufficient funds","name":"Error"}', 400, false);

        self::assertEquals((object) ['error' => 'insufficient funds', 'name' => 'Error', 'status' => 400], $result);
    }

    public function testTimeoutsDefaultAndCanBeChanged(): void
    {
        $sdk = @new BitGoSDK('token', 'tbtc', true);
        self::assertSame(BitGoSDK::DEFAULT_TIMEOUT, $this->getPrivate($sdk, 'timeout'));
        self::assertSame(BitGoSDK::DEFAULT_CONNECT_TIMEOUT, $this->getPrivate($sdk, 'connectTimeout'));

        $sdk->setTimeout(5);
        $sdk->setConnectTimeout(2);

        self::assertSame(5, $this->getPrivate($sdk, 'timeout'));
        self::assertSame(2, $this->getPrivate($sdk, 'connectTimeout'));
    }

    public function testNegativeTimeoutIsRejected(): void
    {
        $express = @new BitGoExpress('127.0.0.1', 1, 'tbtc');

        $this->expectException(\InvalidArgumentException::class);
        $express->setTimeout(-1);
    }
}
