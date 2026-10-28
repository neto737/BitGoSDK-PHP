<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Tests\Fixtures\RecordingTransport;
use PHPUnit\Framework\TestCase;

/**
 * Caller-supplied IDs stay one path segment, so they can't point an authenticated request at another endpoint
 * or add query params.
 */
final class PathSegmentTest extends TestCase
{
    private const API = 'https://app.bitgo-test.com/api/v2/';
    private const TBTC = self::API . 'tbtc';
    private const EXPRESS = 'http://localhost:3080/api/v2/tbtc';

    // Values that would change the URL if they were joined in unescaped.
    private const HOSTILE = ['a/b', '../../../user/me', 'abc?prevId=1', 'a#b', 'a%2Fb', 'a b'];

    /**
     * Each method that puts an ID in the path, as [call with $id, URL before the ID, URL after the ID].
     *
     * @return array<string, array{callable(BitGoSDK, string): mixed, string, string}>
     */
    public static function sdkMethods(): array
    {
        return [
            'getWalletByAddress' => [static fn (BitGoSDK $c, string $id) => $c->getWalletByAddress($id), self::TBTC . '/wallet/address/', ''],
            'getWalletTransfer' => [static fn (BitGoSDK $c, string $id) => $c->getWalletTransfer($id), self::TBTC . '/wallet/w1/transfer/', ''],
            'getWalletTransferBySequenceId' => [static fn (BitGoSDK $c, string $id) => $c->getWalletTransferBySequenceId($id), self::TBTC . '/wallet/w1/transfer/sequenceId/', ''],
            'getWalletAddress' => [static fn (BitGoSDK $c, string $id) => $c->getWalletAddress($id), self::TBTC . '/wallet/w1/address/', ''],
            'updateWalletAddress' => [static fn (BitGoSDK $c, string $id) => $c->updateWalletAddress($id), self::TBTC . '/wallet/w1/address/', ''],
            'getWalletTransaction' => [static fn (BitGoSDK $c, string $id) => $c->getWalletTransaction($id), self::TBTC . '/wallet/w1/tx/', ''],
            'getKeychain' => [static fn (BitGoSDK $c, string $id) => $c->getKeychain($id), self::TBTC . '/key/', ''],
            'getWalletShare' => [static fn (BitGoSDK $c, string $id) => $c->getWalletShare($id), self::TBTC . '/walletshare/', ''],
            'resendWalletShareInvite' => [static fn (BitGoSDK $c, string $id) => $c->resendWalletShareInvite($id), self::TBTC . '/walletshare/', '/resendemail'],
            'cancelWalletShare' => [static fn (BitGoSDK $c, string $id) => $c->cancelWalletShare($id), self::TBTC . '/walletshare/', ''],
            'removeWalletUser' => [static fn (BitGoSDK $c, string $id) => $c->removeWalletUser($id), self::TBTC . '/wallet/w1/user/', ''],
            'getPendingApproval' => [static fn (BitGoSDK $c, string $id) => $c->getPendingApproval($id), self::TBTC . '/pendingapprovals/', ''],
            'simulateWalletWebhook' => [static fn (BitGoSDK $c, string $id) => $c->simulateWalletWebhook($id), self::TBTC . '/wallet/w1/webhooks/', '/simulate'],
            'simulateUserWebhook' => [static fn (BitGoSDK $c, string $id) => $c->simulateUserWebhook($id), self::TBTC . '/webhooks/', '/simulate'],
        ];
    }

    /**
     * @return array<string, array{callable(BitGoExpress, string): mixed, string, string}>
     */
    public static function expressMethods(): array
    {
        return [
            'acceptWalletShare' => [static fn (BitGoExpress $c, string $id) => $c->acceptWalletShare($id), self::EXPRESS . '/walletshare/', '/acceptshare'],
            'updatePendingApproval' => [static fn (BitGoExpress $c, string $id) => $c->updatePendingApproval($id, 'approved', '000000'), self::EXPRESS . '/pendingapprovals/', ''],
        ];
    }

    /**
     * @dataProvider sdkMethods
     * @param callable(BitGoSDK, string): mixed $call
     */
    public function testSdkEscapesIds(callable $call, string $prefix, string $suffix): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $sdk->walletId = 'w1';
        $transport = RecordingTransport::on($sdk);

        foreach (self::HOSTILE as $id) {
            $call($sdk, $id);
            self::assertSame($prefix . rawurlencode($id) . $suffix, $transport->last()->url, $id);
        }
    }

    /**
     * @dataProvider expressMethods
     * @param callable(BitGoExpress, string): mixed $call
     */
    public function testExpressEscapesIds(callable $call, string $prefix, string $suffix): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $transport = RecordingTransport::on($express);

        foreach (self::HOSTILE as $id) {
            $call($express, $id);
            self::assertSame($prefix . rawurlencode($id) . $suffix, $transport->last()->url, $id);
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function endpointChangingIds(): array
    {
        return ['empty' => [''], 'dot' => ['.'], 'dot dot' => ['..']];
    }

    /**
     * An empty ID, '.' or '..' would request the parent endpoint, and percent-encoding can't stop that.
     *
     * @dataProvider endpointChangingIds
     */
    public function testEndpointChangingIdsAreRejected(string $id): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $sdk->walletId = 'w1';
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $sdkTransport = RecordingTransport::on($sdk);
        $expressTransport = RecordingTransport::on($express);
        $calls = array_merge(
            array_map(static fn (array $method) => static fn () => $method[0]($sdk, $id), self::sdkMethods()),
            array_map(static fn (array $method) => static fn () => $method[0]($express, $id), self::expressMethods())
        );

        foreach ($calls as $name => $call) {
            try {
                $call();
                self::fail($name . ' accepted ' . var_export($id, true));
            } catch (\InvalidArgumentException $e) {
                self::addToAssertionCount(1);
            }
        }
        self::assertSame([], $sdkTransport->requests);
        self::assertSame([], $expressTransport->requests);
    }

    /**
     * @return array<string, array{callable(BitGoSDK): mixed, string}>
     */
    public static function sdkWalletMethods(): array
    {
        return [
            'GET' => [static fn (BitGoSDK $c) => $c->getWallet(), ''],
            'POST' => [static fn (BitGoSDK $c) => $c->createWalletAddress(), '/address'],
            'PUT' => [static fn (BitGoSDK $c) => $c->updateWalletAddress('addr1'), '/address/addr1'],
            'DELETE' => [static fn (BitGoSDK $c) => $c->removeWalletUser('u1'), '/user/u1'],
        ];
    }

    /**
     * @dataProvider sdkWalletMethods
     * @param callable(BitGoSDK): mixed $call
     */
    public function testSdkEscapesWalletId(callable $call, string $suffix): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $transport = RecordingTransport::on($sdk);

        foreach (self::HOSTILE as $walletId) {
            $sdk->walletId = $walletId;
            $call($sdk);
            self::assertSame(self::TBTC . '/wallet/' . rawurlencode($walletId) . $suffix, $transport->last()->url, $walletId);
        }
    }

    public function testExpressEscapesWalletId(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $transport = RecordingTransport::on($express);

        foreach (self::HOSTILE as $walletId) {
            $express->walletId = $walletId;
            $express->sendTransaction('address', 1000, 'pass');
            self::assertSame(self::EXPRESS . '/wallet/' . rawurlencode($walletId) . '/sendcoins', $transport->last()->url, $walletId);
        }
    }

    public function testUnsetWalletIdIsRejected(): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        RecordingTransport::on($sdk);
        RecordingTransport::on($express);

        try {
            $sdk->getWallet();
            self::fail('BitGoSDK sent a wallet request without a wallet ID');
        } catch (\InvalidArgumentException $e) {
            self::addToAssertionCount(1);
        }

        $this->expectException(\InvalidArgumentException::class);
        $express->sendTransaction('address', 1000, 'pass');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function ordinaryIds(): array
    {
        return [
            'hex wallet ID' => ['5f8c3e9a1b2c4d5e6f708192a3b4c5d6'],
            'base58 address' => ['2N4Q5FhU2497BryFfUgbqkAJE87aKHUhXMp'],
            'bech32 address' => ['tb1qw508d6qejxtdg4y5r3zarvary0c5xw7kxpjzsx'],
            '0x hash' => ['0x5c504ed432cb51138bcf09aa5e8a410dd4a1e204ef84bfed1be16dfba1b22060'],
            'txid' => ['a1075db55d416d3ca199f55b6084e2115b9345e16c5cf302fc80e9d5fbf5d48d'],
            'XRP address' => ['rLHzPsX6oXkzU2qL12kHCH8G8cnZv1rBJh'],
            'Stellar address' => ['GAJ7Q5Q5Z2NJXQ6VQHSZ3YB5NKRNNLRI4MPRXDVFKLV4EHQ4Z3U5YQ4L'],
        ];
    }

    /**
     * @dataProvider ordinaryIds
     */
    public function testOrdinaryIdsAreSentUnchanged(string $id): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $sdk->walletId = $id;
        $transport = RecordingTransport::on($sdk);

        $sdk->getWalletTransaction($id);

        self::assertSame(self::TBTC . '/wallet/' . $id . '/tx/' . $id, $transport->last()->url);
    }

    /**
     * @return array<string, array{string, callable(BitGoSDK): mixed, string}>
     */
    public static function taggedAddresses(): array
    {
        return [
            'getWalletAddress on txrp' => ['txrp', static fn (BitGoSDK $c) => $c->getWalletAddress('rAddr?dt=123'), self::API . 'txrp/wallet/w1/address/rAddr?dt=123'],
            'getWalletAddress on txrp with dt' => ['txrp', static fn (BitGoSDK $c) => $c->getWalletAddress('rAddr?dt=123', '456'), self::API . 'txrp/wallet/w1/address/rAddr?dt=123&dt=456'],
            'updateWalletAddress on txrp' => ['txrp', static fn (BitGoSDK $c) => $c->updateWalletAddress('rAddr?dt=123', null, null, 'label'), self::API . 'txrp/wallet/w1/address/rAddr?dt=123'],
            'getWalletByAddress on txrp' => ['txrp', static fn (BitGoSDK $c) => $c->getWalletByAddress('rAddr?dt=123'), self::API . 'txrp/wallet/address/rAddr?dt=123'],
            'getWalletAddress on txlm' => ['txlm', static fn (BitGoSDK $c) => $c->getWalletAddress('GAddr?memoId=42'), self::API . 'txlm/wallet/w1/address/GAddr?memoId=42'],
            'updateWalletAddress on txlm' => ['txlm', static fn (BitGoSDK $c) => $c->updateWalletAddress('GAddr?memoId=a b', null, null, 'label'), self::API . 'txlm/wallet/w1/address/GAddr?memoId=a%20b'],
            'getWalletByAddress on txlm' => ['txlm', static fn (BitGoSDK $c) => $c->getWalletByAddress('GAddr?memoId=42'), self::API . 'txlm/wallet/address/GAddr?memoId=42'],
            // The suffix is kept only in its exact form, and only on its own coin.
            'dt on tbtc' => ['tbtc', static fn (BitGoSDK $c) => $c->getWalletAddress('addr?dt=123'), self::TBTC . '/wallet/w1/address/addr%3Fdt%3D123'],
            'memoId on txrp' => ['txrp', static fn (BitGoSDK $c) => $c->getWalletAddress('rAddr?memoId=42'), self::API . 'txrp/wallet/w1/address/rAddr%3FmemoId%3D42'],
            'non-numeric dt' => ['txrp', static fn (BitGoSDK $c) => $c->getWalletAddress('rAddr?dt=1&x=2'), self::API . 'txrp/wallet/w1/address/rAddr%3Fdt%3D1%26x%3D2'],
            'path in address' => ['txrp', static fn (BitGoSDK $c) => $c->getWalletAddress('../x?dt=123'), self::API . 'txrp/wallet/w1/address/..%2Fx?dt=123'],
            'extra query in memoId' => ['txlm', static fn (BitGoSDK $c) => $c->getWalletAddress('GAddr?memoId=1&x=2'), self::API . 'txlm/wallet/w1/address/GAddr%3FmemoId%3D1%26x%3D2'],
        ];
    }

    /**
     * XRP addresses with a destination tag and XLM addresses with a memo keep their suffix as a query string, as before.
     *
     * @dataProvider taggedAddresses
     * @param callable(BitGoSDK): mixed $call
     */
    public function testTaggedAddressesKeepTheirQuery(string $coin, callable $call, string $url): void
    {
        $sdk = new BitGoSDK('token', $coin, true);
        $sdk->walletId = 'w1';
        $transport = RecordingTransport::on($sdk);

        $call($sdk);

        self::assertSame($url, $transport->last()->url);
    }

    public function testTaggedAddressWithoutAddressIsRejected(): void
    {
        $sdk = new BitGoSDK('token', 'txrp', true);
        $sdk->walletId = 'w1';
        RecordingTransport::on($sdk);

        $this->expectException(\InvalidArgumentException::class);
        $sdk->getWalletAddress('..?dt=123');
    }
}
