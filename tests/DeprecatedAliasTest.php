<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Tests\Fixtures\RecordingTransport;
use neto737\BitGoSDK\Tests\Fixtures\RecordingWebhookSdk;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * B13, B14: deprecated method names stay callable and forward their arguments, unchanged, to the method that replaces them.
 */
final class DeprecatedAliasTest extends TestCase
{
    public function testFanoutBuildsTheFanoutUrl(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $express->walletId = 'wallet-id';
        $transport = RecordingTransport::on($express);

        $express->fanoutWalletUnspents('passphrase');

        self::assertSame('http://localhost:3080/api/v2/tbtc/wallet/wallet-id/fanoutunspents', $transport->last()->url);
    }

    public function testFunoutForwardsEveryArgumentToFanout(): void
    {
        $express = new class ('127.0.0.1', 1, 'tbtc') extends BitGoExpress {
            /** @var list<mixed> */
            public array $forwarded = [];

            public function fanoutWalletUnspents(string $walletPassphrase, ?string $xprv = null, ?int $maxNumInputsToUse = null, ?int $numUnspentsToMake = null, ?int $minValue = null, ?int $maxValue = null, ?int $minHeight = null, ?int $maxFeePercentage = null, ?int $minConfirms = null, ?bool $enforceMinConfirmsForChange = null, ?int $feeRate = null, ?int $feeTxConfirmTarget = null)
            {
                $this->forwarded = func_get_args();
                return ['forwarded' => true];
            }
        };
        $args = ['passphrase', 'xprv', 20, 200, 1000, 5000, 700000, 10, 0, false, 2000, 3];

        self::assertSame(['forwarded' => true], $express->funoutWalletUnspents(...$args));
        self::assertSame($args, $express->forwarded);
    }

    public function testAddBlockWebhookForwardsToAddUserWebhook(): void
    {
        $sdk = new RecordingWebhookSdk();

        self::assertSame(['forwarded' => true], $sdk->addBlockWebhook('https://example.com/hook', 'wallet_confirmation', 'label', 0));
        self::assertSame(['addUserWebhook', ['https://example.com/hook', 'wallet_confirmation', 'label', 0]], $sdk->forwarded);
    }

    public function testAddBlockWebhookStillSendsUnknownTypesAsBlock(): void
    {
        $sdk = new RecordingWebhookSdk();

        $sdk->addBlockWebhook('https://example.com/hook', 'transfer');

        self::assertSame(['addUserWebhook', ['https://example.com/hook', 'block', null, null]], $sdk->forwarded);
    }

    public function testListBlockWebhooksForwardsToListUserWebhooks(): void
    {
        $sdk = new RecordingWebhookSdk();

        self::assertSame(['forwarded' => true], $sdk->listBlockWebhooks());
        self::assertSame(['listUserWebhooks', []], $sdk->forwarded);
    }

    public function testRemoveBlockWebhookForwardsToRemoveUserWebhook(): void
    {
        $sdk = new RecordingWebhookSdk();

        self::assertSame(['forwarded' => true], $sdk->removeBlockWebhook('https://example.com/hook'));
        self::assertSame(['removeUserWebhook', ['https://example.com/hook', 'block']], $sdk->forwarded);

        $sdk->removeBlockWebhook('https://example.com/hook', 'wallet_confirmation');
        self::assertSame(['removeUserWebhook', ['https://example.com/hook', 'wallet_confirmation']], $sdk->forwarded);
    }

    /**
     * @return array<string, array{class-string, string, string}>
     */
    public static function deprecatedMethods(): array
    {
        return [
            'funoutWalletUnspents' => [BitGoExpress::class, 'funoutWalletUnspents', 'fanoutWalletUnspents'],
            'addBlockWebhook' => [BitGoSDK::class, 'addBlockWebhook', 'addUserWebhook'],
            'listBlockWebhooks' => [BitGoSDK::class, 'listBlockWebhooks', 'listUserWebhooks'],
            'removeBlockWebhook' => [BitGoSDK::class, 'removeBlockWebhook', 'removeUserWebhook'],
        ];
    }

    /**
     * @dataProvider deprecatedMethods
     * @param class-string $class
     */
    public function testDeprecatedMethodNamesItsReplacement(string $class, string $deprecated, string $replacement): void
    {
        $doc = (string) (new ReflectionMethod($class, $deprecated))->getDocComment();

        self::assertMatchesRegularExpression('/@deprecated\s+2\.3\.0\b.*\b' . $replacement . '\(\)/', $doc);
        self::assertTrue(method_exists($class, $replacement));
        self::assertStringNotContainsString('@deprecated', (string) (new ReflectionMethod($class, $replacement))->getDocComment());
    }
}
