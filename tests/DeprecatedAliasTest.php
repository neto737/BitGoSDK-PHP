<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * B13, B14: deprecated method names stay callable and forward their arguments, unchanged, to the method that replaces them.
 */
final class DeprecatedAliasTest extends TestCase
{
    use AccessesPrivateMembers;

    public function testFanoutBuildsTheFanoutUrl(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');
        $express->walletId = 'wallet-id';

        $express->fanoutWalletUnspents('passphrase');

        self::assertSame('http://127.0.0.1:1/api/v2/tbtc/wallet/wallet-id/fanoutunspents', $this->getPrivate($express, 'url'));
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

    /**
     * @return array<string, array{class-string, string, string}>
     */
    public static function deprecatedMethods(): array
    {
        return [
            'funoutWalletUnspents' => [BitGoExpress::class, 'funoutWalletUnspents', 'fanoutWalletUnspents'],
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
