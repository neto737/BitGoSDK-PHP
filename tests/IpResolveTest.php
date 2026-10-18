<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;

/**
 * B11: requests use IPv4 or IPv6 as the system allows, unless IPv4 is forced.
 */
final class IpResolveTest extends TestCase
{
    use AccessesPrivateMembers;

    public function testSdkDoesNotForceIPv4ByDefault(): void
    {
        $options = $this->invoke(new BitGoSDK('token', 'tbtc'), 'curlOptions', 'GET', []);

        self::assertArrayNotHasKey(CURLOPT_IPRESOLVE, $options);
    }

    public function testSdkCanForceIPv4(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc');

        $sdk->setForceIPv4(true);

        self::assertSame(CURL_IPRESOLVE_V4, $this->invoke($sdk, 'curlOptions', 'GET', [])[CURLOPT_IPRESOLVE]);
    }

    public function testSdkResolvesTheRequestIpVersion(): void
    {
        $sdk = new BitGoSDK('token', 'btc');

        $sdk->setRequestIP('192.0.2.10');
        self::assertSame(CURL_IPRESOLVE_V4, $this->invoke($sdk, 'curlOptions', 'GET', [])[CURLOPT_IPRESOLVE]);

        $sdk->setRequestIP('2001:db8::10');
        self::assertSame(CURL_IPRESOLVE_V6, $this->invoke($sdk, 'curlOptions', 'GET', [])[CURLOPT_IPRESOLVE]);
    }

    public function testExpressDoesNotForceIPv4ByDefault(): void
    {
        $options = $this->invoke(new BitGoExpress('127.0.0.1', 1, 'tbtc'), 'curlOptions', 'POST', []);

        self::assertArrayNotHasKey(CURLOPT_IPRESOLVE, $options);
    }

    public function testExpressCanForceIPv4(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');

        $express->setForceIPv4(true);

        self::assertSame(CURL_IPRESOLVE_V4, $this->invoke($express, 'curlOptions', 'POST', [])[CURLOPT_IPRESOLVE]);
    }
}
