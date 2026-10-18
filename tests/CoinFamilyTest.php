<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;
use ReflectionClassConstant;

/**
 * B10: coin-specific params are gated on coin families that cover testnet codes,
 * and the UTXO family holds UTXO coins only.
 */
final class CoinFamilyTest extends TestCase
{
    use AccessesPrivateMembers;

    /**
     * @return array<string, array{string, string, bool}>
     */
    public function expressProvider(): array
    {
        return [
            'btc is UTXO' => ['UTXO_COINS', 'btc', true],
            'doge is UTXO' => ['UTXO_COINS', 'doge', true],
            'tdoge is UTXO' => ['UTXO_COINS', 'tdoge', true],
            'tbtg is UTXO' => ['UTXO_COINS', 'tbtg', true],
            'stx is not UTXO' => ['UTXO_COINS', 'stx', false],
            'tstx is not UTXO' => ['UTXO_COINS', 'tstx', false],
            'eth is not UTXO' => ['UTXO_COINS', 'eth', false],
            'tbtc is BTC' => ['BTC_COINS', 'tbtc', true],
            'ltc is not BTC' => ['BTC_COINS', 'ltc', false],
            'tltc is SegWit' => ['SEGWIT_COINS', 'tltc', true],
            'tbtg is SegWit' => ['SEGWIT_COINS', 'tbtg', true],
            'bch is not SegWit' => ['SEGWIT_COINS', 'bch', false],
            'teth is ETH' => ['ETH_COINS', 'teth', true],
            'hteth is ETH' => ['ETH_COINS', 'hteth', true],
            'txrp is XRP' => ['XRP_COINS', 'txrp', true],
        ];
    }

    /**
     * @dataProvider expressProvider
     */
    public function testExpressCoinFamilies(string $family, string $coin, bool $expected): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, $coin);

        self::assertSame($expected, $this->invoke($express, 'coinIn', self::family(BitGoExpress::class, $family)));
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public function sdkProvider(): array
    {
        return [
            'eth is ETH' => ['ETH_COINS', 'eth', true],
            'teth is ETH' => ['ETH_COINS', 'teth', true],
            'gteth is ETH' => ['ETH_COINS', 'gteth', true],
            'hteth is ETH' => ['ETH_COINS', 'hteth', true],
            'tbtc is not ETH' => ['ETH_COINS', 'tbtc', false],
            'txrp is XRP' => ['XRP_COINS', 'txrp', true],
            'txlm is XLM' => ['XLM_COINS', 'txlm', true],
            'xrp is not XLM' => ['XLM_COINS', 'xrp', false],
        ];
    }

    /**
     * @dataProvider sdkProvider
     */
    public function testSdkCoinFamilies(string $family, string $coin, bool $expected): void
    {
        $sdk = new BitGoSDK('token', $coin, true);

        self::assertSame($expected, $this->invoke($sdk, 'coinIn', self::family(BitGoSDK::class, $family)));
    }

    /**
     * @param class-string $class
     * @return mixed
     */
    private static function family(string $class, string $name)
    {
        return (new ReflectionClassConstant($class, $name))->getValue();
    }
}
