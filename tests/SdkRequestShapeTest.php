<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Enum\AddressType;
use neto737\BitGoSDK\Tests\Fixtures\RecordingTransport;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Every public BitGoSDK request method sends the expected method, URL, headers and body.
 */
final class SdkRequestShapeTest extends TestCase
{
    private const API = 'https://app.bitgo-test.com/api/v2/';
    private const TBTC = self::API . 'tbtc';
    private const HOOK = 'https://example.com/hook';
    private const POLICY_RULE = ['id' => 'r1', 'type' => 'velocityLimit', 'condition' => '{"amount":1}', 'action' => 'deny'];

    // Methods that configure the client instead of sending a request.
    private const NOT_REQUESTS = ['setRequestIP', 'setCaFile', 'setTimeout', 'setConnectTimeout', 'setForceIPv4'];

    /**
     * @return array<string, array{string, callable(BitGoSDK): mixed, string, string, array<string, mixed>|null}>
     */
    public static function requests(): array
    {
        return [
            // Auth
            'getCurrentUserProfile' => ['tbtc', static fn (BitGoSDK $c) => $c->getCurrentUserProfile(), 'GET', self::API . 'user/me', null],
            'getSessionInfo' => ['tbtc', static fn (BitGoSDK $c) => $c->getSessionInfo(), 'GET', self::API . 'user/session', null],
            'lockSession' => ['tbtc', static fn (BitGoSDK $c) => $c->lockSession(), 'POST', self::API . 'user/lock', []],
            'unlockSession' => ['tbtc', static fn (BitGoSDK $c) => $c->unlockSession('000000', 300), 'POST', self::API . 'user/unlock', ['otp' => '000000', 'duration' => 300]],
            'logout' => ['tbtc', static fn (BitGoSDK $c) => $c->logout(), 'GET', self::API . 'user/logout', null],

            // Wallets
            'listWallets' => ['tbtc', static fn (BitGoSDK $c) => $c->listWallets(10, 'prev', true), 'GET', self::TBTC . '/wallet?limit=10&prevId=prev', null],
            'listWallets on teth' => ['teth', static fn (BitGoSDK $c) => $c->listWallets(10, 'prev', true), 'GET', self::API . 'teth/wallet?limit=10&prevId=prev&allTokens=true', null],
            'getWallet' => ['tbtc', static fn (BitGoSDK $c) => $c->getWallet(true), 'GET', self::TBTC . '/wallet/w1', null],
            'getWallet on teth' => ['teth', static fn (BitGoSDK $c) => $c->getWallet(), 'GET', self::API . 'teth/wallet/w1?allTokens=false', null],
            'getWalletByAddress' => ['tbtc', static fn (BitGoSDK $c) => $c->getWalletByAddress('addr1'), 'GET', self::TBTC . '/wallet/address/addr1', null],
            'getTotalBalances' => ['tbtc', static fn (BitGoSDK $c) => $c->getTotalBalances(), 'GET', self::TBTC . '/wallet/balances', null],
            'getMaximumSpendable' => [
                'tbtc',
                static fn (BitGoSDK $c) => $c->getMaximumSpendable(200, 1000, 5000, 700000, 2000, 1, false),
                'GET',
                self::TBTC . '/wallet/w1/maximumSpendable?limit=200&minValue=1000&maxValue=5000&minHeight=700000&feeRate=2000&minConfirms=1&enforceMinConfirmsForChange=false',
                null,
            ],
            'freezeWallet' => ['tbtc', static fn (BitGoSDK $c) => $c->freezeWallet('000000', 60), 'POST', self::TBTC . '/wallet/w1/freeze', ['otp' => '000000', 'duration' => 60]],

            // Transfers and transactions
            'listWalletTransfers' => ['tbtc', static fn (BitGoSDK $c) => $c->listWalletTransfers('prev', true), 'GET', self::TBTC . '/wallet/w1/transfer?prevId=prev', null],
            'listWalletTransfers on teth' => ['teth', static fn (BitGoSDK $c) => $c->listWalletTransfers('prev', true), 'GET', self::API . 'teth/wallet/w1/transfer?prevId=prev&allTokens=true', null],
            'getWalletTransfer' => ['tbtc', static fn (BitGoSDK $c) => $c->getWalletTransfer('t1'), 'GET', self::TBTC . '/wallet/w1/transfer/t1', null],
            'getWalletTransferBySequenceId' => ['tbtc', static fn (BitGoSDK $c) => $c->getWalletTransferBySequenceId('seq1'), 'GET', self::TBTC . '/wallet/w1/transfer/sequenceId/seq1', null],
            'getWalletTransactions' => ['tbtc', static fn (BitGoSDK $c) => $c->getWalletTransactions('prev', true), 'GET', self::TBTC . '/wallet/w1/tx?prevId=prev', null],
            'getWalletTransactions on teth' => ['teth', static fn (BitGoSDK $c) => $c->getWalletTransactions('prev', true), 'GET', self::API . 'teth/wallet/w1/tx?prevId=prev&allTokens=true', null],
            'getWalletTransaction' => ['tbtc', static fn (BitGoSDK $c) => $c->getWalletTransaction('tx1'), 'GET', self::TBTC . '/wallet/w1/tx/tx1', null],
            'listWalletUnspents' => [
                'tbtc',
                static fn (BitGoSDK $c) => $c->listWalletUnspents('prev', 1000, 5000, 700000, 0),
                'GET',
                self::TBTC . '/wallet/w1/unspents?prevId=prev&minValue=1000&maxValue=5000&minHeight=700000&minConfirms=0',
                null,
            ],
            'estimateTransactionFees' => ['tbtc', static fn (BitGoSDK $c) => $c->estimateTransactionFees(3), 'GET', self::TBTC . '/tx/fee?numBlocks=3', null],
            'getMarketPriceData' => ['tbtc', static fn (BitGoSDK $c) => $c->getMarketPriceData(), 'GET', self::TBTC . '/market/latest', null],

            // Addresses
            'createWalletAddress' => [
                'tbtc',
                static fn (BitGoSDK $c) => $c->createWalletAddress(AddressType::P2SH_P2WSH_DEPOSIT, 'label', true, 30),
                'POST',
                self::TBTC . '/wallet/w1/address',
                ['chain' => 10, 'label' => 'label'],
            ],
            'createWalletAddress on teth' => [
                'teth',
                static fn (BitGoSDK $c) => $c->createWalletAddress(AddressType::LEGACY_DEPOSIT, 'label', true, 30),
                'POST',
                self::API . 'teth/wallet/w1/address',
                ['chain' => 0, 'label' => 'label', 'lowPriority' => true, 'gasPrice' => 30],
            ],
            'getWalletAddress' => ['tbtc', static fn (BitGoSDK $c) => $c->getWalletAddress('addr1', '123', 'memo'), 'GET', self::TBTC . '/wallet/w1/address/addr1', null],
            'getWalletAddress on txrp' => ['txrp', static fn (BitGoSDK $c) => $c->getWalletAddress('addr1', '123', 'memo'), 'GET', self::API . 'txrp/wallet/w1/address/addr1?dt=123', null],
            'getWalletAddress on txlm' => ['txlm', static fn (BitGoSDK $c) => $c->getWalletAddress('addr1', '123', 'memo'), 'GET', self::API . 'txlm/wallet/w1/address/addr1?memoId=memo', null],
            'updateWalletAddress' => ['tbtc', static fn (BitGoSDK $c) => $c->updateWalletAddress('addr1', '123', 'memo', 'label'), 'PUT', self::TBTC . '/wallet/w1/address/addr1', ['label' => 'label']],
            'updateWalletAddress on txrp' => [
                'txrp',
                static fn (BitGoSDK $c) => $c->updateWalletAddress('addr1', '123', 'memo', 'label'),
                'PUT',
                self::API . 'txrp/wallet/w1/address/addr1',
                ['dt' => '123', 'label' => 'label'],
            ],
            'updateWalletAddress on txlm' => [
                'txlm',
                static fn (BitGoSDK $c) => $c->updateWalletAddress('addr1', '123', 'memo', 'label'),
                'PUT',
                self::API . 'txlm/wallet/w1/address/addr1',
                ['memoId' => 'memo', 'label' => 'label'],
            ],
            'getWalletAddresses' => [
                'tbtc',
                static fn (BitGoSDK $c) => $c->getWalletAddresses('lab', 0, false, 'prev', [0, 10], -1),
                'GET',
                self::TBTC . '/wallet/w1/addresses?labelContains=lab&limit=0&mine=false&prevId=prev&chains%5B0%5D=0&chains%5B1%5D=10&sort=-1',
                null,
            ],

            // Keychains
            'listKeychains' => ['tbtc', static fn (BitGoSDK $c) => $c->listKeychains(10, 'prev'), 'GET', self::TBTC . '/key?limit=10&prevId=prev', null],
            'getKeychain' => ['tbtc', static fn (BitGoSDK $c) => $c->getKeychain('k1'), 'GET', self::TBTC . '/key/k1', null],
            'createBitGoKeychain' => ['tbtc', static fn (BitGoSDK $c) => $c->createBitGoKeychain('bitgo', 'ent1', true), 'POST', self::TBTC . '/key', ['source' => 'bitgo']],
            'createBitGoKeychain on teth' => [
                'teth',
                static fn (BitGoSDK $c) => $c->createBitGoKeychain('bitgo', 'ent1', true),
                'POST',
                self::API . 'teth/key',
                ['source' => 'bitgo', 'enterprise' => 'ent1', 'newFeeAddress' => true],
            ],
            'createBackupKeychain' => ['tbtc', static fn (BitGoSDK $c) => $c->createBackupKeychain('cme', 'backup'), 'POST', self::TBTC . '/key', ['source' => 'backup', 'provider' => 'cme']],
            'addKeychain' => ['tbtc', static fn (BitGoSDK $c) => $c->addKeychain('xpub1', 'enc1', 'user'), 'POST', self::TBTC . '/key', ['pub' => 'xpub1', 'encryptedPrv' => 'enc1', 'source' => 'user']],

            // Policy
            'setVelocityPolicyRule' => ['tbtc', static fn (BitGoSDK $c) => $c->setVelocityPolicyRule(...array_values(self::POLICY_RULE)), 'POST', self::TBTC . '/wallet/w1/policy/rule', self::POLICY_RULE],
            'updateVelocityPolicyRule' => ['tbtc', static fn (BitGoSDK $c) => $c->updateVelocityPolicyRule(...array_values(self::POLICY_RULE)), 'PUT', self::TBTC . '/wallet/w1/policy/rule', self::POLICY_RULE],
            'removePolicyRule' => ['tbtc', static fn (BitGoSDK $c) => $c->removePolicyRule(...array_values(self::POLICY_RULE)), 'DELETE', self::TBTC . '/wallet/w1/policy/rule', self::POLICY_RULE],

            // Sharing
            'listWalletShares' => ['tbtc', static fn (BitGoSDK $c) => $c->listWalletShares(), 'GET', self::TBTC . '/walletshare', null],
            'getWalletShare' => ['tbtc', static fn (BitGoSDK $c) => $c->getWalletShare('s1'), 'GET', self::TBTC . '/walletshare/s1', null],
            'resendWalletShareInvite' => ['tbtc', static fn (BitGoSDK $c) => $c->resendWalletShareInvite('s1'), 'POST', self::TBTC . '/walletshare/s1/resendemail', []],
            'cancelWalletShare' => ['tbtc', static fn (BitGoSDK $c) => $c->cancelWalletShare('s1'), 'DELETE', self::TBTC . '/walletshare/s1', ['walletShareId' => 's1']],
            'removeWalletUser' => ['tbtc', static fn (BitGoSDK $c) => $c->removeWalletUser('u1'), 'DELETE', self::TBTC . '/wallet/w1/user/u1', ['walletId' => 'w1', 'userId' => 'u1']],

            // Pending approvals
            'getPendingApproval' => ['tbtc', static fn (BitGoSDK $c) => $c->getPendingApproval('p1'), 'GET', self::TBTC . '/pendingapprovals/p1', null],
            'listPendingApprovals' => ['tbtc', static fn (BitGoSDK $c) => $c->listPendingApprovals('w2', 'ent1', true), 'GET', self::TBTC . '/pendingapprovals?walletID=w2', null],
            'listPendingApprovals on teth' => [
                'teth',
                static fn (BitGoSDK $c) => $c->listPendingApprovals('w2', 'ent1', true),
                'GET',
                self::API . 'teth/pendingapprovals?walletID=w2&enterprise=ent1&allTokens=true',
                null,
            ],

            // Wallet webhooks
            'listWalletWebhooks' => ['tbtc', static fn (BitGoSDK $c) => $c->listWalletWebhooks(true), 'GET', self::TBTC . '/wallet/w1/webhooks', null],
            'listWalletWebhooks on teth' => ['teth', static fn (BitGoSDK $c) => $c->listWalletWebhooks(true), 'GET', self::API . 'teth/wallet/w1/webhooks?allTokens=true', null],
            'addWalletWebhook' => [
                'tbtc',
                static fn (BitGoSDK $c) => $c->addWalletWebhook(self::HOOK, 'transfer', 0),
                'POST',
                self::TBTC . '/wallet/w1/webhooks',
                ['url' => self::HOOK, 'type' => 'transfer', 'numConfirmations' => 0],
            ],
            'removeWalletWebhook' => ['tbtc', static fn (BitGoSDK $c) => $c->removeWalletWebhook(self::HOOK, 'transfer'), 'DELETE', self::TBTC . '/wallet/w1/webhooks', ['url' => self::HOOK, 'type' => 'transfer']],
            'simulateWalletWebhook' => [
                'tbtc',
                static fn (BitGoSDK $c) => $c->simulateWalletWebhook('h1', 't1', 'p1'),
                'POST',
                self::TBTC . '/wallet/w1/webhooks/h1/simulate',
                ['webhookId' => 'h1', 'transferId' => 't1', 'pendingApprovalId' => 'p1'],
            ],

            // User webhooks
            'listUserWebhooks' => ['tbtc', static fn (BitGoSDK $c) => $c->listUserWebhooks(), 'GET', self::TBTC . '/webhooks', null],
            'addUserWebhook' => [
                'tbtc',
                static fn (BitGoSDK $c) => $c->addUserWebhook(self::HOOK, 'block', 'label', 2),
                'POST',
                self::TBTC . '/webhooks',
                ['url' => self::HOOK, 'type' => 'block', 'label' => 'label', 'numConfirmations' => 2],
            ],
            'removeUserWebhook' => ['tbtc', static fn (BitGoSDK $c) => $c->removeUserWebhook(self::HOOK, 'block'), 'DELETE', self::TBTC . '/webhooks', ['url' => self::HOOK, 'type' => 'block']],
            'simulateUserWebhook' => ['tbtc', static fn (BitGoSDK $c) => $c->simulateUserWebhook('h1', 'b1'), 'POST', self::TBTC . '/webhooks/h1/simulate', ['webhookId' => 'h1', 'blockId' => 'b1']],
            'addBlockWebhook' => [
                'tbtc',
                static fn (BitGoSDK $c) => $c->addBlockWebhook(self::HOOK, 'wallet_confirmation', 'label', 2),
                'POST',
                self::TBTC . '/webhooks',
                ['url' => self::HOOK, 'type' => 'wallet_confirmation', 'label' => 'label', 'numConfirmations' => 2],
            ],
            'listBlockWebhooks' => ['tbtc', static fn (BitGoSDK $c) => $c->listBlockWebhooks(), 'GET', self::TBTC . '/webhooks', null],
            'removeBlockWebhook' => ['tbtc', static fn (BitGoSDK $c) => $c->removeBlockWebhook(self::HOOK), 'DELETE', self::TBTC . '/webhooks', ['url' => self::HOOK, 'type' => 'block']],
        ];
    }

    /**
     * @dataProvider requests
     * @param callable(BitGoSDK): mixed $call
     * @param array<string, mixed>|null $body
     */
    public function testRequestShape(string $coin, callable $call, string $method, string $url, ?array $body): void
    {
        $sdk = new BitGoSDK('v2xtoken', $coin, true);
        $sdk->walletId = 'w1';
        $transport = RecordingTransport::on($sdk);

        $call($sdk);

        self::assertCount(1, $transport->requests);
        $request = $transport->last();
        self::assertSame($method, $request->method);
        self::assertSame($url, $request->url);
        self::assertSame(['Content-Type: application/json', 'Authorization: Bearer v2xtoken', 'User-Agent: BitGoSDK-PHP/' . BitGoSDK::VERSION], $request->headers);
        self::assertSame($body, $request->json());
    }

    public function testEveryRequestMethodHasAShapeTest(): void
    {
        $tested = array_unique(array_map(static function (string $name): string {
            return explode(' ', $name)[0];
        }, array_keys(self::requests())));

        $methods = array_filter((new ReflectionClass(BitGoSDK::class))->getMethods(ReflectionMethod::IS_PUBLIC), static function (ReflectionMethod $method): bool {
            return !$method->isStatic() && !$method->isConstructor() && !in_array($method->getName(), self::NOT_REQUESTS, true);
        });
        $untested = array_diff(array_map(static function (ReflectionMethod $method): string {
            return $method->getName();
        }, $methods), $tested);

        self::assertSame([], array_values($untested));
    }

    public function testBodyWithoutParamsIsAnEmptyJsonObject(): void
    {
        $sdk = new BitGoSDK('v2xtoken', 'tbtc', true);
        $transport = RecordingTransport::on($sdk);

        $sdk->lockSession();

        self::assertSame('{}', $transport->last()->body);
    }

    public function testProductionUsesTheProductionEndpoint(): void
    {
        $sdk = new BitGoSDK('v2xtoken', 'btc');
        $sdk->walletId = 'w1';
        $transport = RecordingTransport::on($sdk);

        $sdk->getWallet();
        $sdk->getCurrentUserProfile();

        self::assertSame('https://app.bitgo.com/api/v2/btc/wallet/w1', $transport->requests[0]->url);
        self::assertSame('https://app.bitgo.com/api/v2/user/me', $transport->requests[1]->url);
    }

    public function testResponseBodyIsDecoded(): void
    {
        $sdk = new BitGoSDK('v2xtoken', 'tbtc', true);
        $sdk->walletId = 'w1';
        RecordingTransport::on($sdk)->respond('{"id":"w1","balance":0}');

        self::assertSame(['id' => 'w1', 'balance' => 0], $sdk->getWallet());
    }
}
