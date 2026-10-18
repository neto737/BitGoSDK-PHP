<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * B1: request params must not leak from one call into the next.
 * B2: only null params are dropped, so 0, false and '' are still sent.
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

    public function testSdkDropsOnlyNullParams(): void
    {
        $sdk = @new BitGoSDK('token', 'tbtc', true);

        self::assertSame(
            ['limit' => 0, 'mine' => false, 'label' => ''],
            $this->invoke($sdk, 'withoutNulls', ['limit' => 0, 'prevId' => null, 'mine' => false, 'label' => ''])
        );
    }

    public function testExpressDropsOnlyNullParams(): void
    {
        $express = @new BitGoExpress('127.0.0.1', 1, 'tbtc');

        self::assertSame(
            ['amount' => 0, 'noSplitChange' => false],
            $this->invoke($express, 'withoutNulls', ['amount' => 0, 'comment' => null, 'noSplitChange' => false])
        );
    }

    public function testSdkQuerySendsBooleansAsWords(): void
    {
        $sdk = @new BitGoSDK('token', 'tbtc', true);

        self::assertSame(
            'limit=0&mine=false&allTokens=true',
            $this->invoke($sdk, 'buildQuery', ['limit' => 0, 'mine' => false, 'allTokens' => true])
        );
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
     * @param mixed ...$args
     * @return mixed
     */
    private function invoke($client, string $name, ...$args)
    {
        $method = new \ReflectionMethod($client, $name);
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }
        return $method->invoke($client, ...$args);
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
