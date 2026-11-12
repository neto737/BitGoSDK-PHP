<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\CoinCode;
use neto737\BitGoSDK\Wallets;

/**
 * BitGo::wallets(): every coin's wallets at once.
 */
final class AllWalletsTest extends TestCase
{
    private function wallets(): Wallets
    {
        return $this->bitgo()->wallets();
    }

    public function testListYieldsTheWalletsOfEveryCoin(): void
    {
        $this->respond(['wallets' => [['id' => 'w1', 'coin' => 'tbtc'], ['id' => 'w2', 'coin' => 'hteth']]]);

        $list = $this->wallets()->list();

        self::assertSame([], $this->requests(), 'list() sent a request before iterating.');
        self::assertSame([['id' => 'w1', 'coin' => 'tbtc'], ['id' => 'w2', 'coin' => 'hteth']], iterator_to_array($list));
        self::assertRequest($this->lastRequest(), 'GET', self::API . 'wallets', null);
    }

    public function testListSendsItsFiltersAndTakesCoinCodes(): void
    {
        $this->respond(['wallets' => []]);

        iterator_to_array($this->wallets()->list(
            limit: 50,
            coin: [CoinCode::TBTC, 'ttao'],
            enterprise: ['e1'],
            type: ['hot', 'cold'],
            labelContains: 'Treasury',
            deleted: false,
            expandBalance: true,
            excludeSpendableBalance: true,
            extra: ['skipUnsupportedCoins' => true],
        ));

        self::assertRequest(
            $this->lastRequest(),
            'GET',
            self::API . 'wallets?skipUnsupportedCoins=true&limit=50&coin%5B0%5D=tbtc&coin%5B1%5D=ttao&enterprise%5B0%5D=e1'
                . '&type%5B0%5D=hot&type%5B1%5D=cold&labelContains=Treasury&deleted=false&expandBalance=true&excludeSpendableBalance=true',
            null,
        );
    }

    public function testListFollowsNextBatchPrevId(): void
    {
        $this->respond(['wallets' => [['id' => 'w1']], 'nextBatchPrevId' => 'w1']);
        $this->respond(['wallets' => [['id' => 'w2']]]);

        self::assertSame([['id' => 'w1'], ['id' => 'w2']], iterator_to_array($this->wallets()->list(limit: 1)));
        self::assertRequest($this->requests()[0], 'GET', self::API . 'wallets?limit=1', null);
        self::assertRequest($this->requests()[1], 'GET', self::API . 'wallets?limit=1&prevId=w1', null);
    }

    public function testBalances(): void
    {
        $totals = ['balances' => [['coin' => 'tbtc', 'balanceString' => '150000', 'confirmedBalanceString' => '100000']]];
        $this->respond($totals);

        self::assertSame($totals, $this->wallets()->balances());
        self::assertRequest($this->lastRequest(), 'GET', self::API . 'wallet/balances', null);
    }

    public function testBalancesSendsItsFilters(): void
    {
        $this->wallets()->balances(
            coin: [CoinCode::HTETH],
            enterprise: ['e1'],
            type: ['hot'],
            labelContains: 'Treasury',
            allTokens: true,
            excludeEmptyBalances: true,
            excludeSpendableBalances: false,
            includeStakingBalances: true,
            extra: ['skipTxCounts' => true],
        );

        self::assertRequest(
            $this->lastRequest(),
            'GET',
            self::API . 'wallet/balances?skipTxCounts=true&coin%5B0%5D=hteth&enterprise%5B0%5D=e1&type%5B0%5D=hot&labelContains=Treasury'
                . '&allTokens=true&excludeEmptyBalances=true&excludeSpendableBalances=false&includeStakingBalances=true',
            null,
        );
    }
}
