<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * B1: request params must not leak from one call into the next.
 *
 * Until requests go through a capturable transport, these tests point the client
 * at a closed local port and inspect the internal params state after the call.
 *
 * Clients are constructed with `@` to silence the dynamic-property deprecations
 * tracked as B8; drop it once those properties are declared.
 */
final class RequestParamsTest extends TestCase
{
    private const UNREACHABLE_URL = 'http://127.0.0.1:1/';

    public function testSdkClearsParamsAfterEachCall(): void
    {
        $sdk = @new BitGoSDK('token', 'tbtc', true);
        $this->setPrivate($sdk, 'params', ['otp' => '000000', 'duration' => 600]);
        $this->setPrivate($sdk, 'url', self::UNREACHABLE_URL);

        $this->execute($sdk, 'POST');

        self::assertSame([], $this->getPrivate($sdk, 'params'));
    }

    public function testExpressClearsParamsAfterEachCall(): void
    {
        $express = @new BitGoExpress('127.0.0.1', 1, 'tbtc');
        $this->setPrivate($express, 'params', ['walletPassphrase' => 'secret']);
        $this->setPrivate($express, 'url', self::UNREACHABLE_URL);

        $this->execute($express, 'POST');

        self::assertSame([], $this->getPrivate($express, 'params'));
    }

    /**
     * @param object $client
     */
    private function execute($client, string $requestType): void
    {
        $method = new \ReflectionMethod($client, '__execute');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }
        $method->invoke($client, $requestType);
    }

    /**
     * @param object $client
     * @param mixed $value
     */
    private function setPrivate($client, string $name, $value): void
    {
        $property = new ReflectionProperty($client, $name);
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }
        $property->setValue($client, $value);
    }

    /**
     * @param object $client
     * @return mixed
     */
    private function getPrivate($client, string $name)
    {
        $property = new ReflectionProperty($client, $name);
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }
        return $property->getValue($client);
    }
}
