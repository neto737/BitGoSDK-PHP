<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\Amount;
use neto737\BitGoSDK\Wallet;

final class WalletTest extends TestCase
{
    private const string URL = self::API . 'tbtc/wallet/w1';

    private function wallet(): Wallet
    {
        return $this->bitgo()->coin('tbtc')->wallet('w1');
    }

    public function testUpdateSendsOnlyTheParamsPassed(): void
    {
        $this->respond(['id' => 'w1', 'coin' => 'tbtc', 'label' => 'Cold storage']);

        self::assertSame(['id' => 'w1', 'coin' => 'tbtc', 'label' => 'Cold storage'], $this->wallet()->update(label: 'Cold storage'));
        self::assertRequest($this->lastRequest(), 'PUT', self::URL, ['label' => 'Cold storage']);
    }

    public function testUpdateSendsEveryNamedParamAndExtra(): void
    {
        $this->wallet()->update(
            label: 'Hot',
            approvalsRequired: 2,
            disableTransactionNotifications: true,
            buildDefaults: ['txFormat' => 'psbt', 'feeMultiplier' => 1.5],
            coinSpecific: ['tbtc' => ['allowQuantumUnsafeAddresses' => false]],
            extra: ['userKeySigningRequired' => true, 'label' => 'ignored'],
        );

        self::assertRequest($this->lastRequest(), 'PUT', self::URL, [
            'userKeySigningRequired' => true,
            'label' => 'Hot',
            'approvalsRequired' => 2,
            'disableTransactionNotifications' => true,
            'buildDefaults' => ['txFormat' => 'psbt', 'feeMultiplier' => 1.5],
            'coinSpecific' => ['tbtc' => ['allowQuantumUnsafeAddresses' => false]],
        ]);
    }

    public function testFreezeDefaultsToOneHour(): void
    {
        $this->respond(['time' => '2026-11-12T10:00:00.000Z', 'expires' => '2026-11-12T11:00:00.000Z']);

        self::assertSame(['time' => '2026-11-12T10:00:00.000Z', 'expires' => '2026-11-12T11:00:00.000Z'], $this->wallet()->freeze());
        self::assertRequest($this->lastRequest(), 'POST', self::URL . '/freeze', ['duration' => 3600]);
    }

    public function testFreezeSendsItsParams(): void
    {
        $this->wallet()->freeze(duration: 86400, reason: 'UserRequest', referenceId: 'incident-7', extra: ['otp' => '000000']);

        self::assertRequest($this->lastRequest(), 'POST', self::URL . '/freeze', [
            'otp' => '000000',
            'duration' => 86400,
            'reason' => 'UserRequest',
            'referenceId' => 'incident-7',
        ]);
    }

    public function testMaximumSpendable(): void
    {
        $this->respond(['coin' => 'tbtc', 'maximumSpendable' => '99812']);

        self::assertSame(['coin' => 'tbtc', 'maximumSpendable' => '99812'], $this->wallet()->maximumSpendable());
        self::assertRequest($this->lastRequest(), 'GET', self::URL . '/maximumSpendable', null);
    }

    public function testMaximumSpendableSendsItsFilters(): void
    {
        $this->wallet()->maximumSpendable(
            limit: 200,
            minValue: Amount::fromDisplay('0.0001', decimals: 8),
            maxValue: '5000000',
            minHeight: 100,
            minConfirms: 2,
            enforceMinConfirmsForChange: true,
            feeRate: 20000,
            maxFeeRate: 50000,
            numBlocks: 6,
            unspentIds: ['tx1:0', 'tx2:1'],
        );

        self::assertRequest(
            $this->lastRequest(),
            'GET',
            self::URL . '/maximumSpendable?limit=200&minValue=10000&maxValue=5000000&minHeight=100&minConfirms=2&enforceMinConfirmsForChange=true'
                . '&feeRate=20000&maxFeeRate=50000&numBlocks=6&unspentIds%5B0%5D=tx1%3A0&unspentIds%5B1%5D=tx2%3A1',
            null,
        );
    }
}
