<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use PHPUnit\Framework\TestCase;

/**
 * B12: ping() pings BitGo through Express, and pingExpress() pings Express alone.
 *
 * The client points at a closed local port, so each call fails fast and the URL it built can be inspected.
 */
final class ExpressPingTest extends TestCase
{
    use AccessesPrivateMembers;

    public function testPingGoesThroughExpressToBitGo(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');

        $express->ping();

        self::assertSame('http://127.0.0.1:1/api/v2/ping', $this->getPrivate($express, 'url'));
    }

    public function testPingExpressPingsExpressOnly(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');

        $express->pingExpress();

        self::assertSame('http://127.0.0.1:1/api/v2/pingexpress', $this->getPrivate($express, 'url'));
    }
}
