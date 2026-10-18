<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Enum\AddressType;
use neto737\BitGoSDK\Enum\CurrencyCode;
use PHPUnit\Framework\TestCase;

/**
 * Placeholder suite so CI has something to run until the request-shape tests land.
 */
final class SmokeTest extends TestCase
{
    public function testClassesAutoload(): void
    {
        self::assertTrue(class_exists(BitGoSDK::class));
        self::assertTrue(class_exists(BitGoExpress::class));
    }

    public function testEndpointConstants(): void
    {
        self::assertSame('https://app.bitgo.com/api/v2/', BitGoSDK::BITGO_PRODUCTION_API_ENDPOINT);
        self::assertSame('https://app.bitgo-test.com/api/v2/', BitGoSDK::BITGO_TESTNET_API_ENDPOINT);
    }

    public function testEnumValues(): void
    {
        self::assertSame('btc', CurrencyCode::BITCOIN);
        self::assertSame(0, AddressType::LEGACY_DEPOSIT);
    }
}
