<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;

/**
 * B1: request params must not leak from one call into the next.
 * B2: only null params are dropped, so 0, false and '' are still sent.
 *
 * Until requests go through a capturable transport, these tests point the client
 * at a closed local port and inspect the internal params state after the call.
 */
final class RequestParamsTest extends TestCase
{
    use AccessesPrivateMembers;

    private const UNREACHABLE_URL = 'http://127.0.0.1:1/';

    public function testSdkClearsParamsAfterEachCall(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $this->setPrivate($sdk, 'params', ['otp' => '000000', 'duration' => 600]);
        $this->setPrivate($sdk, 'url', self::UNREACHABLE_URL);

        $this->execute($sdk, 'POST');

        self::assertSame([], $this->getPrivate($sdk, 'params'));
    }

    public function testExpressClearsParamsAfterEachCall(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');
        $this->setPrivate($express, 'params', ['walletPassphrase' => 'secret']);
        $this->setPrivate($express, 'url', self::UNREACHABLE_URL);

        $this->execute($express, 'POST');

        self::assertSame([], $this->getPrivate($express, 'params'));
    }

    public function testSdkDropsOnlyNullParams(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);

        self::assertSame(
            ['limit' => 0, 'mine' => false, 'label' => ''],
            $this->invoke($sdk, 'withoutNulls', ['limit' => 0, 'prevId' => null, 'mine' => false, 'label' => ''])
        );
    }

    public function testExpressDropsOnlyNullParams(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');

        self::assertSame(
            ['amount' => 0, 'noSplitChange' => false],
            $this->invoke($express, 'withoutNulls', ['amount' => 0, 'comment' => null, 'noSplitChange' => false])
        );
    }

    public function testSdkQuerySendsBooleansAsWords(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);

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
        $this->invoke($client, '__execute', $requestType);
    }
}
