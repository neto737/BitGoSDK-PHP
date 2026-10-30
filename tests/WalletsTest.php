<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\Coin\Wallets;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use neto737\BitGoSDK\Exception\NotFoundException;

final class WalletsTest extends TestCase
{
    private const string URL = self::API . 'tbtc/wallet';

    private function wallets(): Wallets
    {
        return $this->bitgo()->coin('tbtc')->wallets();
    }

    public function testListSendsItsFiltersAndYieldsTheWallets(): void
    {
        $this->respond(['coin' => 'tbtc', 'wallets' => [['id' => 'w1'], ['id' => 'w2']]]);

        $list = $this->wallets()->list(limit: 100, allTokens: true, excludeSpendableBalance: false, searchLabel: 'Hot', showAllWallets: true);

        self::assertSame([], $this->requests(), 'list() sent a request before iterating.');
        self::assertSame([['id' => 'w1'], ['id' => 'w2']], iterator_to_array($list));
        self::assertRequest($this->lastRequest(), 'GET', self::URL . '?limit=100&allTokens=true&excludeSpendableBalance=false&searchLabel=Hot&showAllWallets=true', null);
    }

    public function testListFollowsNextBatchPrevId(): void
    {
        $this->respond(['coin' => 'tbtc', 'wallets' => [['id' => 'w1']], 'nextBatchPrevId' => 'w1']);
        $this->respond(['coin' => 'tbtc', 'wallets' => [['id' => 'w2']]]);

        self::assertSame([['id' => 'w1'], ['id' => 'w2']], iterator_to_array($this->wallets()->list(limit: 1)));
        self::assertCount(2, $this->requests());
        self::assertRequest($this->requests()[0], 'GET', self::URL . '?limit=1', null);
        self::assertRequest($this->requests()[1], 'GET', self::URL . '?limit=1&prevId=w1', null);
    }

    public function testFindByAddress(): void
    {
        $this->respond(['id' => 'w1', 'coin' => 'tbtc', 'label' => 'Hot']);

        self::assertSame(['id' => 'w1', 'coin' => 'tbtc', 'label' => 'Hot'], $this->wallets()->findByAddress('2N8hwP1WmJrFF5QWABn38y63uYLhnJYJYTF'));
        self::assertRequest($this->lastRequest(), 'GET', self::URL . '/address/2N8hwP1WmJrFF5QWABn38y63uYLhnJYJYTF', null);
    }

    public function testTaggedXrpAddressIsEscapedWhole(): void
    {
        $this->bitgo()->coin('txrp')->wallets()->findByAddress('rLHzPsX6oXkzU2qL12kHCH8G8cnZv1rBJh?dt=123');

        self::assertRequest($this->lastRequest(), 'GET', self::API . 'txrp/wallet/address/rLHzPsX6oXkzU2qL12kHCH8G8cnZv1rBJh%3Fdt%3D123', null);
    }

    public function testFindByAnEmptyAddressIsRejectedBeforeAnyRequest(): void
    {
        try {
            $this->wallets()->findByAddress('');
            self::fail('No exception was thrown.');
        } catch (InvalidArgumentException) {
            self::assertSame([], $this->requests());
        }
    }

    public function testFindByAnUnknownAddressThrowsNotFound(): void
    {
        $this->respond('', 404);

        $this->expectException(NotFoundException::class);
        $this->wallets()->findByAddress('2NunknownAddress');
    }
}
