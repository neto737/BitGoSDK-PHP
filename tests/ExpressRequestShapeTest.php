<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Tests\Fixtures\RecordingTransport;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Every public BitGoExpress request method sends the expected method, URL, headers and body.
 */
final class ExpressRequestShapeTest extends TestCase
{
    private const API = 'http://localhost:3080/api/v2/';
    private const TBTC = self::API . 'tbtc';

    // Methods that configure the client instead of sending a request.
    private const NOT_REQUESTS = ['setCaFile', 'setVerifyTls', 'setTimeout', 'setConnectTimeout', 'setForceIPv4'];

    // Arguments shared by sendTransaction() and sendTransactionToMany() after the recipient(s):
    // walletPassphrase, prv, numBlocks, feeRate, comment, unspents, minConfirms, enforceMinConfirmsForChange, targetWalletUnspents,
    // noSplitChange, minValue, maxValue, gasPrice, gasLimit, sequenceId, segwit, lastLedgerSequence, ledgerSequenceDelta
    private const SEND_ARGS = ['pass', 'prv1', 2, 2000, 'comment', ['tx1:0'], 1, false, 10, true, 100, 5000, 30, 21000, 7, true, 900, '10'];
    private const UTXO_SEND_BODY = [
        'walletPassphrase' => 'pass',
        'prv' => 'prv1',
        'numBlocks' => 2,
        'feeRate' => 2000,
        'comment' => 'comment',
        'unspents' => ['tx1:0'],
        'minConfirms' => 1,
        'enforceMinConfirmsForChange' => false,
        'targetWalletUnspents' => 10,
        'noSplitChange' => true,
        'minValue' => 100,
        'maxValue' => 5000,
        'segwit' => true,
    ];
    private const ETH_SEND_BODY = ['walletPassphrase' => 'pass', 'prv' => 'prv1', 'comment' => 'comment', 'gasPrice' => 30, 'gasLimit' => 21000, 'sequenceId' => 7];
    private const XRP_SEND_BODY = ['walletPassphrase' => 'pass', 'prv' => 'prv1', 'comment' => 'comment', 'lastLedgerSequence' => 900, 'ledgerSequenceDelta' => '10'];

    // walletPassphrase, xprv, maxNumInputsToUse, numUnspentsToMake, minValue, maxValue, minHeight, maxFeePercentage, minConfirms,
    // enforceMinConfirmsForChange, feeRate, feeTxConfirmTarget
    private const FANOUT_ARGS = ['pass', 'xprv1', 20, 200, null, 5000, 700000, 10, 1, false, 2000, 3];
    private const FANOUT_BODY = [
        'walletPassphrase' => 'pass',
        'xprv' => 'xprv1',
        'maxNumInputsToUse' => 20,
        'numUnspentsToMake' => 200,
        'maxValue' => 5000,
        'minHeight' => 700000,
        'maxFeePercentage' => 10,
        'minConfirms' => 1,
        'enforceMinConfirmsForChange' => false,
        'feeRate' => 2000,
        'feeTxConfirmTarget' => 3,
    ];

    /**
     * @return array<string, array{string, callable(BitGoExpress): mixed, string, string, array<string, mixed>|null}>
     */
    public static function requests(): array
    {
        return [
            // Auth and health. login() is covered by testLoginRequestShape(), since it sends no token.
            'ping' => ['tbtc', static fn (BitGoExpress $c) => $c->ping(), 'GET', self::API . 'ping', null],
            'pingExpress' => ['tbtc', static fn (BitGoExpress $c) => $c->pingExpress(), 'GET', self::API . 'pingexpress', null],

            // Wallets
            'generateWallet' => [
                'tbtc',
                static fn (BitGoExpress $c) => $c->generateWallet('label', 'pass', 'xpubU', 'xpubB', 'keyternal', 'ent1', true, 30, 'code'),
                'POST',
                self::TBTC . '/wallet/generate',
                [
                    'label' => 'label',
                    'passphrase' => 'pass',
                    'userKey' => 'xpubU',
                    'backupXpub' => 'xpubB',
                    'backupXpubProvider' => 'keyternal',
                    'enterprise' => 'ent1',
                    'disableTransactionNotifications' => true,
                    'passcodeEncryptionCode' => 'code',
                ],
            ],
            'generateWallet on teth' => [
                'teth',
                static fn (BitGoExpress $c) => $c->generateWallet('label', 'pass', null, null, null, 'ent1', null, 30),
                'POST',
                self::API . 'teth/wallet/generate',
                ['label' => 'label', 'passphrase' => 'pass', 'enterprise' => 'ent1', 'gasPrice' => 30],
            ],
            'addWallet' => [
                'tbtc',
                static fn (BitGoExpress $c) => $c->addWallet('label', 2, 3, ['k1', 'k2', 'k3'], 'ent1', false, true),
                'POST',
                self::TBTC . '/wallet',
                ['label' => 'label', 'm' => 2, 'n' => 3, 'keys' => ['k1', 'k2', 'k3'], 'enterprise' => 'ent1', 'isCold' => false, 'disableTransactionNotifications' => true],
            ],

            // Sending
            'sendTransaction' => [
                'tbtc',
                static fn (BitGoExpress $c) => $c->sendTransaction('addr1', 1000, ...self::SEND_ARGS),
                'POST',
                self::TBTC . '/wallet/w1/sendcoins',
                ['address' => 'addr1', 'amount' => '1000'] + self::UTXO_SEND_BODY,
            ],
            'sendTransaction on teth' => [
                'teth',
                static fn (BitGoExpress $c) => $c->sendTransaction('addr1', '10000000000000000000', ...self::SEND_ARGS),
                'POST',
                self::API . 'teth/wallet/w1/sendcoins',
                ['address' => 'addr1', 'amount' => '10000000000000000000'] + self::ETH_SEND_BODY,
            ],
            'sendTransaction on txrp' => [
                'txrp',
                static fn (BitGoExpress $c) => $c->sendTransaction('addr1', 1000, ...self::SEND_ARGS),
                'POST',
                self::API . 'txrp/wallet/w1/sendcoins',
                ['address' => 'addr1', 'amount' => '1000'] + self::XRP_SEND_BODY,
            ],
            'sendTransactionToMany' => [
                'tbtc',
                static fn (BitGoExpress $c) => $c->sendTransactionToMany([['address' => 'a1', 'amount' => 1000], ['address' => 'a2', 'amount' => '2000']], ...self::SEND_ARGS),
                'POST',
                self::TBTC . '/wallet/w1/sendmany',
                ['recipients' => [['address' => 'a1', 'amount' => '1000'], ['address' => 'a2', 'amount' => '2000']]] + self::UTXO_SEND_BODY,
            ],
            'sendTransactionToMany on teth' => [
                'teth',
                static fn (BitGoExpress $c) => $c->sendTransactionToMany([['address' => 'a1', 'amount' => 1000]], ...self::SEND_ARGS),
                'POST',
                self::API . 'teth/wallet/w1/sendmany',
                ['recipients' => [['address' => 'a1', 'amount' => '1000']]] + self::ETH_SEND_BODY,
            ],

            // UTXO management
            'consolidateWalletUnspents' => [
                'tbtc',
                static fn (BitGoExpress $c) => $c->consolidateWalletUnspents('pass', 1, 200, 1000, 5000, 700000, 2000, 3, 10, 1, false),
                'POST',
                self::TBTC . '/wallet/w1/consolidateunspents',
                [
                    'walletPassphrase' => 'pass',
                    'numUnspentsToMake' => 1,
                    'limit' => 200,
                    'minValue' => 1000,
                    'maxValue' => 5000,
                    'minHeight' => 700000,
                    'feeRate' => 2000,
                    'feeTxConfirmTarget' => 3,
                    'minConfirms' => 1,
                    'enforceMinConfirmsForChange' => false,
                ],
            ],
            'consolidateWalletUnspents on tltc' => [
                'tltc',
                static fn (BitGoExpress $c) => $c->consolidateWalletUnspents('pass', null, null, null, null, null, null, 3, 10),
                'POST',
                self::API . 'tltc/wallet/w1/consolidateunspents',
                ['walletPassphrase' => 'pass', 'maxFeePercentage' => 10],
            ],
            'fanoutWalletUnspents' => ['tbtc', static fn (BitGoExpress $c) => $c->fanoutWalletUnspents(...self::FANOUT_ARGS), 'POST', self::TBTC . '/wallet/w1/fanoutunspents', self::FANOUT_BODY],
            'funoutWalletUnspents' => ['tbtc', static fn (BitGoExpress $c) => $c->funoutWalletUnspents(...self::FANOUT_ARGS), 'POST', self::TBTC . '/wallet/w1/fanoutunspents', self::FANOUT_BODY],
            'sweep' => [
                'tbtc',
                static fn (BitGoExpress $c) => $c->sweep('addr1', 'pass', 'xprv1', '000000', 2000, 3, 30, 7, 900, '10'),
                'POST',
                self::TBTC . '/wallet/w1/sweep',
                ['address' => 'addr1', 'walletPassphrase' => 'pass', 'xprv' => 'xprv1', 'otp' => '000000', 'feeRate' => 2000, 'feeTxConfirmTarget' => 3, 'sequenceId' => 7],
            ],
            'sweep on txrp' => [
                'txrp',
                static fn (BitGoExpress $c) => $c->sweep('addr1', 'pass', null, null, 2000, 3, 30, null, 900, '10'),
                'POST',
                self::API . 'txrp/wallet/w1/sweep',
                ['address' => 'addr1', 'walletPassphrase' => 'pass', 'lastLedgerSequence' => 900, 'ledgerSequenceDelta' => '10'],
            ],

            // Manual flow
            'buildTransaction' => [
                'tbtc',
                static fn (BitGoExpress $c) => $c->buildTransaction([['address' => 'a1', 'amount' => 1000]], 2, 2000, 1, false, ['tx1:0'], 10, true, 100, 5000, 30, 900, '10'),
                'POST',
                self::TBTC . '/wallet/w1/tx/build',
                [
                    'recipients' => [['address' => 'a1', 'amount' => '1000']],
                    'numBlocks' => 2,
                    'feeRate' => 2000,
                    'minConfirms' => 1,
                    'enforceMinConfirmsForChange' => false,
                    'unspents' => ['tx1:0'],
                    'targetWalletUnspents' => 10,
                    'noSplitChange' => true,
                    'minValue' => 100,
                    'maxValue' => 5000,
                ],
            ],
            'buildTransaction on teth' => [
                'teth',
                static fn (BitGoExpress $c) => $c->buildTransaction([['address' => 'a1', 'amount' => 1000]], 2, 2000, null, null, null, null, null, null, null, 30),
                'POST',
                self::API . 'teth/wallet/w1/tx/build',
                ['recipients' => [['address' => 'a1', 'amount' => '1000']], 'gasPrice' => 30],
            ],
            'signBuildTransaction' => [
                'tbtc',
                static fn (BitGoExpress $c) => $c->signBuildTransaction((object) ['txHex' => '00'], 'prv1', 'seed', (object) ['encryptedPrv' => 'enc'], 'pass'),
                'POST',
                self::TBTC . '/wallet/w1/signtx',
                ['txPrebuild' => ['txHex' => '00'], 'prv' => 'prv1', 'coldDerivationSeed' => 'seed', 'keychain' => ['encryptedPrv' => 'enc'], 'walletPassphrase' => 'pass'],
            ],
            'sendBuildTransaction' => [
                'tbtc',
                static fn (BitGoExpress $c) => $c->sendBuildTransaction((object) ['txHex' => '00'], '000000', '00', 'comment'),
                'POST',
                self::TBTC . '/wallet/w1/tx/send',
                ['halfSigned' => ['txHex' => '00'], 'otp' => '000000', 'txHex' => '00', 'comment' => 'comment'],
            ],

            // Keychains, sharing and approvals
            'createKeychain' => ['tbtc', static fn (BitGoExpress $c) => $c->createKeychain(), 'POST', self::TBTC . '/keychain/local', []],
            'shareWallet' => [
                'tbtc',
                static fn (BitGoExpress $c) => $c->shareWallet('user@example.com', 'view,spend', 'pass', false, true),
                'POST',
                self::TBTC . '/wallet/w1/share',
                ['email' => 'user@example.com', 'permissions' => 'view,spend', 'walletPassphrase' => 'pass', 'skipKeychain' => false, 'disableEmail' => true],
            ],
            'acceptWalletShare' => [
                'tbtc',
                static fn (BitGoExpress $c) => $c->acceptWalletShare('s1', 'newpass', 'password', 'enc'),
                'POST',
                self::TBTC . '/walletshare/s1/acceptshare',
                ['walletShareId' => 's1', 'newWalletPassphrase' => 'newpass', 'userPassword' => 'password', 'overrideEncryptedPrv' => 'enc'],
            ],
            'updatePendingApproval' => [
                'tbtc',
                static fn (BitGoExpress $c) => $c->updatePendingApproval('p1', 'approved', '000000'),
                'PUT',
                self::TBTC . '/pendingapprovals/p1',
                ['state' => 'approved', 'otp' => '000000'],
            ],

            // Utilities
            'encrypt' => ['tbtc', static fn (BitGoExpress $c) => $c->encrypt('plain', 'pw'), 'POST', self::API . 'encrypt', ['input' => 'plain', 'password' => 'pw']],
            'decrypt' => ['tbtc', static fn (BitGoExpress $c) => $c->decrypt('cipher', 'pw'), 'POST', self::API . 'decrypt', ['input' => 'cipher', 'password' => 'pw']],
            'verifyAddress' => ['tbtc', static fn (BitGoExpress $c) => $c->verifyAddress('addr1'), 'POST', self::TBTC . '/verifyaddress', ['address' => 'addr1']],
        ];
    }

    /**
     * @dataProvider requests
     * @param callable(BitGoExpress): mixed $call
     * @param array<string, mixed>|null $body
     */
    public function testRequestShape(string $coin, callable $call, string $method, string $url, ?array $body): void
    {
        $express = new BitGoExpress('localhost', 3080, $coin);
        $express->accessToken = 'v2xtoken';
        $express->walletId = 'w1';
        $transport = RecordingTransport::on($express);

        $call($express);

        self::assertCount(1, $transport->requests);
        $request = $transport->last();
        self::assertSame($method, $request->method);
        self::assertSame($url, $request->url);
        self::assertSame(['Content-Type: application/json', 'Authorization: Bearer v2xtoken', 'User-Agent: BitGoSDK-PHP/' . BitGoSDK::VERSION], $request->headers);
        self::assertSame($body, $request->json());
    }

    public function testLoginRequestShape(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $express->accessToken = 'v2xold';
        $transport = RecordingTransport::on($express);

        $express->login('user@example.com', 'password', '000000', true);

        $request = $transport->last();
        self::assertSame('POST', $request->method);
        self::assertSame(self::API . 'user/login', $request->url);
        self::assertSame(['Content-Type: application/json', 'User-Agent: BitGoSDK-PHP/' . BitGoSDK::VERSION], $request->headers);
        self::assertSame(['email' => 'user@example.com', 'password' => 'password', 'otp' => '000000', 'extensible' => true], $request->json());
    }

    public function testEveryRequestMethodHasAShapeTest(): void
    {
        $tested = array_unique(array_map(static function (string $name): string {
            return explode(' ', $name)[0];
        }, array_merge(array_keys(self::requests()), ['login'])));

        $methods = array_filter((new ReflectionClass(BitGoExpress::class))->getMethods(ReflectionMethod::IS_PUBLIC), static function (ReflectionMethod $method): bool {
            return !$method->isStatic() && !$method->isConstructor() && !in_array($method->getName(), self::NOT_REQUESTS, true);
        });
        $untested = array_diff(array_map(static function (ReflectionMethod $method): string {
            return $method->getName();
        }, $methods), $tested);

        self::assertSame([], array_values($untested));
    }

    public function testBodyWithoutParamsIsAnEmptyJsonObject(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $transport = RecordingTransport::on($express);

        $express->createKeychain();

        self::assertSame('{}', $transport->last()->body);
    }

    public function testHttpsExpressIsReachedOverHttps(): void
    {
        $express = new BitGoExpress('https://express.internal', 3443, 'tbtc');
        $transport = RecordingTransport::on($express);

        $express->pingExpress();

        self::assertSame('https://express.internal:3443/api/v2/pingexpress', $transport->last()->url);
    }

    public function testObjectResponsesAreDecodedAsObjects(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        RecordingTransport::on($express)->respond('{"txHex":"00","feeInfo":{"fee":100}}');

        self::assertEquals((object) ['txHex' => '00', 'feeInfo' => (object) ['fee' => 100]], $express->buildTransaction([]));
    }
}
