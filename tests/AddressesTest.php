<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\Wallet\Addresses;

final class AddressesTest extends TestCase
{
    private const URL = self::API . 'tbtc/wallet/w1/address';
    private const XRP_TAGGED = 'rLHzPsX6oXkzU2qL12kHCH8G8cnZv1rBJh?dt=123';

    private function addresses(): Addresses
    {
        return $this->bitgo()->coin('tbtc')->wallet('w1')->addresses();
    }

    public function testCreateWithDefaultsLetsBitGoChoose(): void
    {
        $this->respond(['id' => 'a1', 'address' => '2N…']);

        self::assertSame(['id' => 'a1', 'address' => '2N…'], $this->addresses()->create());
        self::assertRequest($this->lastRequest(), 'POST', self::URL, []);
    }

    public function testCreateSendsEveryNamedParam(): void
    {
        $this->addresses()->create(chain: 0, label: 'Deposit 42', lowPriority: false, forwarderVersion: 4, onToken: 'hteth:usdc', format: 'cashaddr', extra: ['evmKeyRingReferenceAddress' => '0xabc']);

        self::assertRequest($this->lastRequest(), 'POST', self::URL, [
            'evmKeyRingReferenceAddress' => '0xabc',
            'chain' => 0,
            'label' => 'Deposit 42',
            'lowPriority' => false,
            'forwarderVersion' => 4,
            'onToken' => 'hteth:usdc',
            'format' => 'cashaddr',
        ]);
    }

    public function testGet(): void
    {
        $this->addresses()->get('2N8hwP1WmJrFF5QWABn38y63uYLhnJYJYTF');

        self::assertRequest($this->lastRequest(), 'GET', self::URL . '/2N8hwP1WmJrFF5QWABn38y63uYLhnJYJYTF', null);
    }

    public function testTaggedXrpAddressIsEscapedWhole(): void
    {
        $this->addresses()->get(self::XRP_TAGGED);

        self::assertRequest($this->lastRequest(), 'GET', self::URL . '/rLHzPsX6oXkzU2qL12kHCH8G8cnZv1rBJh%3Fdt%3D123', null);
    }

    public function testUpdate(): void
    {
        $this->addresses()->update(self::XRP_TAGGED, label: 'Renamed');

        self::assertRequest($this->lastRequest(), 'PUT', self::URL . '/rLHzPsX6oXkzU2qL12kHCH8G8cnZv1rBJh%3Fdt%3D123', ['label' => 'Renamed']);
    }

    public function testListSendsItsFiltersAndYieldsTheAddresses(): void
    {
        $this->respond(['addresses' => [['id' => 'a1'], ['id' => 'a2']]]);

        $list = $this->addresses()->list(labelContains: 'Deposit', limit: 50, mine: true, sort: -1, chains: [0, 10], includeBalances: false, returnBalancesForToken: 'hteth:usdc', pendingDeployment: false);

        self::assertSame([], $this->requests(), 'list() sent a request before iterating.');
        self::assertSame([['id' => 'a1'], ['id' => 'a2']], iterator_to_array($list));
        self::assertRequest(
            $this->lastRequest(),
            'GET',
            self::API . 'tbtc/wallet/w1/addresses?labelContains=Deposit&limit=50&mine=true&sort=-1&chains%5B0%5D=0&chains%5B1%5D=10&includeBalances=false&returnBalancesForToken=hteth%3Ausdc&pendingDeployment=false',
            null
        );
    }
}
