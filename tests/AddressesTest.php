<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\Exception\ApiException;
use neto737\BitGoSDK\Exception\AuthenticationException;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use neto737\BitGoSDK\Wallet\Addresses;
use PHPUnit\Framework\Attributes\DataProvider;

final class AddressesTest extends TestCase
{
    private const string URL = self::API . 'tbtc/wallet/w1/address';
    private const string XRP_TAGGED = 'rLHzPsX6oXkzU2qL12kHCH8G8cnZv1rBJh?dt=123';

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

    public function testDeployWithDefaultsLetsBitGoChoose(): void
    {
        $this->respond(['txId' => '0xdeploy']);

        self::assertSame(['txId' => '0xdeploy'], $this->addresses()->deploy('0xabc'));
        self::assertRequest($this->lastRequest(), 'POST', self::URL . '/0xabc/deployment', []);
    }

    public function testDeploySendsEveryNamedParam(): void
    {
        $this->addresses()->deploy('0xabc', forceDeploy: true, gasPrice: 30000000000, eip1559: ['maxFeePerGas' => 40000000000, 'maxPriorityFeePerGas' => 2000000000], extra: ['newParam' => 1]);

        self::assertRequest($this->lastRequest(), 'POST', self::URL . '/0xabc/deployment', [
            'newParam' => 1,
            'forceDeploy' => true,
            'gasPrice' => 30000000000,
            'eip1559' => ['maxFeePerGas' => 40000000000, 'maxPriorityFeePerGas' => 2000000000],
        ]);
    }

    public function testDeployThatBitGoRejectsThrows(): void
    {
        $this->respond(['error' => 'address is already deployed', 'name' => 'AddressAlreadyDeployed', 'requestId' => 'req-1'], 400);

        try {
            $this->addresses()->deploy('0xabc');
            self::fail('No exception was thrown.');
        } catch (ApiException $e) {
            self::assertSame(400, $e->getStatusCode());
            self::assertSame('AddressAlreadyDeployed', $e->getErrorName());
        }
    }

    public function testForwardTokensWithDefaultsLetsBitGoChoose(): void
    {
        $this->respond(['txId' => '0xforward']);

        self::assertSame(['txId' => '0xforward'], $this->addresses()->forwardTokens('0xabc'));
        self::assertRequest($this->lastRequest(), 'POST', self::URL . '/0xabc/tokenforward', []);
    }

    public function testForwardTokensSendsEveryNamedParam(): void
    {
        $this->addresses()->forwardTokens('0xabc', tokenName: 'hteth:usdc', forceFlush: false, gasPrice: 30000000000, eip1559: ['maxFeePerGas' => 40000000000, 'maxPriorityFeePerGas' => 2000000000], extra: ['newParam' => 1]);

        self::assertRequest($this->lastRequest(), 'POST', self::URL . '/0xabc/tokenforward', [
            'newParam' => 1,
            'tokenName' => 'hteth:usdc',
            'forceFlush' => false,
            'gasPrice' => 30000000000,
            'eip1559' => ['maxFeePerGas' => 40000000000, 'maxPriorityFeePerGas' => 2000000000],
        ]);
    }

    public function testGetOwnershipProof(): void
    {
        $proof = ['iou' => false, 'address' => '2N8hwP1WmJrFF5QWABn38y63uYLhnJYJYTF', 'signature' => 'sig', 'proofType' => 'BITCOIN_P2SH_P2WSH'];
        $this->respond($proof);

        self::assertSame($proof, $this->addresses()->getOwnershipProof('2N8hwP1WmJrFF5QWABn38y63uYLhnJYJYTF'));
        self::assertRequest($this->lastRequest(), 'GET', self::URL . '/2N8hwP1WmJrFF5QWABn38y63uYLhnJYJYTF/proof', null);
    }

    public function testOwnershipProofWithoutAccessThrowsAuthenticationException(): void
    {
        $this->respond(['error' => 'VASP ID is not configured', 'requestId' => 'req-1'], 403);

        $this->expectException(AuthenticationException::class);
        $this->addresses()->getOwnershipProof('2N8hwP1WmJrFF5QWABn38y63uYLhnJYJYTF');
    }

    /**
     * @return array<string, array{\Closure(Addresses): mixed}>
     */
    public static function verbsWithAnEmptyAddress(): array
    {
        return [
            'get' => [static fn (Addresses $addresses): array => $addresses->get('')],
            'update' => [static fn (Addresses $addresses): array => $addresses->update('')],
            'deploy' => [static fn (Addresses $addresses): array => $addresses->deploy('')],
            'forwardTokens' => [static fn (Addresses $addresses): array => $addresses->forwardTokens('')],
            'getOwnershipProof' => [static fn (Addresses $addresses): array => $addresses->getOwnershipProof('')],
        ];
    }

    /**
     * @param \Closure(Addresses): mixed $call
     */
    #[DataProvider('verbsWithAnEmptyAddress')]
    public function testAnEmptyAddressIsRejectedBeforeAnyRequest(\Closure $call): void
    {
        try {
            $call($this->addresses());
            self::fail('No exception was thrown.');
        } catch (InvalidArgumentException) {
            self::assertSame([], $this->requests());
        }
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
