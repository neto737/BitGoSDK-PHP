<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\Tests\Fixtures\RecordingTransport;
use PHPUnit\Framework\TestCase;

/**
 * B12: ping() pings BitGo through Express, and pingExpress() pings Express alone.
 */
final class ExpressPingTest extends TestCase
{
    public function testPingGoesThroughExpressToBitGo(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $transport = RecordingTransport::on($express);

        $express->ping();

        self::assertSame('http://localhost:3080/api/v2/ping', $transport->last()->url);
    }

    public function testPingExpressPingsExpressOnly(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $transport = RecordingTransport::on($express);

        $express->pingExpress();

        self::assertSame('http://localhost:3080/api/v2/pingexpress', $transport->last()->url);
    }
}
