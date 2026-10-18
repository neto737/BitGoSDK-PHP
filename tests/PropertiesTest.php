<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;

/**
 * B8: the constructors only set declared properties, so PHP 8.2+ raises no dynamic-property deprecation.
 */
final class PropertiesTest extends TestCase
{
    public function testSdkConstructsWithoutDeprecations(): void
    {
        $sdk = $this->constructWithoutDeprecations(static function (): BitGoSDK {
            return new BitGoSDK('token', 'tbtc', true);
        });

        self::assertSame('token', $sdk->accessToken);
        self::assertSame('tbtc', $sdk->coin);
        self::assertTrue($sdk->testNet);
    }

    public function testExpressConstructsWithoutDeprecations(): void
    {
        $express = $this->constructWithoutDeprecations(static function (): BitGoExpress {
            return new BitGoExpress('localhost', 3080, 'tbtc');
        });

        self::assertSame('localhost', $express->hostname);
        self::assertSame(3080, $express->port);
        self::assertSame('tbtc', $express->coin);
    }

    /**
     * @template T of object
     * @param callable(): T $construct
     * @return T
     */
    private function constructWithoutDeprecations(callable $construct)
    {
        $deprecations = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$deprecations): bool {
            $deprecations[] = $errstr;
            return true;
        }, E_DEPRECATED | E_USER_DEPRECATED);

        try {
            $client = $construct();
        } finally {
            restore_error_handler();
        }

        self::assertSame([], $deprecations);
        return $client;
    }
}
