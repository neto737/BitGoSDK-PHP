<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Tests\Fixtures\RecordingTransport;
use PHPUnit\Framework\TestCase;

/**
 * B1: request params must not leak from one call into the next.
 * B2: only null params are dropped, so 0, false and '' are still sent.
 */
final class RequestParamsTest extends TestCase
{
    public function testSdkParamsDoNotLeakIntoTheNextCall(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $transport = RecordingTransport::on($sdk);

        $sdk->unlockSession('000000', 600);
        $sdk->lockSession();
        $sdk->getTotalBalances();

        self::assertSame('{}', $transport->requests[1]->body);
        self::assertSame('https://app.bitgo-test.com/api/v2/tbtc/wallet/balances', $transport->requests[2]->url);
    }

    public function testSdkParamsDoNotLeakAfterAFailedCall(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $transport = RecordingTransport::on($sdk)->fail(CURLE_COULDNT_CONNECT, 'Connection refused');

        $sdk->unlockSession('000000', 600);
        $sdk->lockSession();

        self::assertSame('{}', $transport->last()->body);
    }

    public function testExpressParamsDoNotLeakIntoTheNextCall(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $express->walletId = 'w1';
        $transport = RecordingTransport::on($express);

        $express->sendTransaction('address', 1000, 'secret');
        $express->createKeychain();

        self::assertSame('{}', $transport->last()->body);
    }

    public function testSdkSendsFalsyBodyParams(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $sdk->walletId = 'w1';
        $transport = RecordingTransport::on($sdk);

        $sdk->createWalletAddress(0, '');

        self::assertSame(['chain' => 0, 'label' => ''], $transport->last()->json());
    }

    public function testSdkSendsFalsyQueryParams(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $sdk->walletId = 'w1';
        $transport = RecordingTransport::on($sdk);

        $sdk->getWalletAddresses('', 0, false);

        self::assertSame('https://app.bitgo-test.com/api/v2/tbtc/wallet/w1/addresses?labelContains=&limit=0&mine=false', $transport->last()->url);
    }

    public function testExpressSendsFalsyParams(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $express->walletId = 'w1';
        $transport = RecordingTransport::on($express);

        $express->sendTransaction('address', 0, '', null, null, null, '', null, 0, false);

        self::assertSame(
            ['address' => 'address', 'amount' => '0', 'walletPassphrase' => '', 'comment' => '', 'minConfirms' => 0, 'enforceMinConfirmsForChange' => false],
            $transport->last()->json()
        );
    }
}
