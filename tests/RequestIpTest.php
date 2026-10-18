<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;

/**
 * B18: setRequestIP() says whether the IP was applied.
 */
final class RequestIpTest extends TestCase
{
    use AccessesPrivateMembers;

    /**
     * @return array<string, array{string}>
     */
    public static function validIps(): array
    {
        return [
            'IPv4' => ['192.0.2.10'],
            'IPv6' => ['2001:db8::10'],
        ];
    }

    /**
     * @dataProvider validIps
     */
    public function testValidIpOnMainnetIsApplied(string $ip): void
    {
        $sdk = new BitGoSDK('token', 'btc');

        self::assertTrue($sdk->setRequestIP($ip));
        self::assertSame($ip, $this->getPrivate($sdk, 'requestIP'));
    }

    public function testInvalidIpIsNotApplied(): void
    {
        $sdk = new BitGoSDK('token', 'btc');

        self::assertFalse($sdk->setRequestIP('not-an-ip'));
        self::assertNull($this->getPrivate($sdk, 'requestIP'));
    }

    public function testRejectedIpKeepsTheEarlierOne(): void
    {
        $sdk = new BitGoSDK('token', 'btc');
        $sdk->setRequestIP('192.0.2.10');

        self::assertFalse($sdk->setRequestIP('192.0.2.300'));
        self::assertSame('192.0.2.10', $this->getPrivate($sdk, 'requestIP'));
    }

    public function testIpIsNotAppliedOnTestnet(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);

        self::assertFalse($sdk->setRequestIP('192.0.2.10'));
        self::assertNull($this->getPrivate($sdk, 'requestIP'));
    }
}
