<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\Amount;
use neto737\BitGoSDK\CoinCode;

final class CoinCodeTest extends TestCase
{
    public function testAnEnumAndAStringSendTheSameRequest(): void
    {
        $this->bitgo()->coin(CoinCode::TBTC)->wallet('w1')->get();
        $this->bitgo()->coin('tbtc')->wallet('w1')->get();

        [$fromEnum, $fromString] = $this->requests();
        self::assertRequest($fromEnum, 'GET', self::API . 'tbtc/wallet/w1', null);
        self::assertSame((string) $fromString->getUri(), (string) $fromEnum->getUri());
        self::assertSame('tbtc', $this->bitgo()->coin(CoinCode::TBTC)->code);
    }

    public function testACodeMissingFromTheEnumWorksAsAString(): void
    {
        $this->bitgo()->coin('ttao')->wallet('w1')->get();

        self::assertRequest($this->lastRequest(), 'GET', self::API . 'ttao/wallet/w1', null);
    }

    public function testEachCaseIsNamedAfterItsCode(): void
    {
        foreach (CoinCode::cases() as $coin) {
            self::assertSame(strtolower($coin->name), $coin->value);
        }
    }

    /**
     * Decimals from BitGo's public asset list (GET /api/v1/all/publicAssets), production and test, on 2026-10-30.
     */
    public function testDecimalsMatchBitGosAssetList(): void
    {
        $expected = [
            'btc' => 8, 'tbtc' => 8, 'tbtc4' => 8, 'bch' => 8, 'tbch' => 8, 'ltc' => 8, 'tltc' => 8,
            'doge' => 8, 'tdoge' => 8, 'dash' => 8, 'tdash' => 8, 'zec' => 8, 'tzec' => 8,
            'eth' => 18, 'hteth' => 18, 'sepeth' => 18, 'polygon' => 18, 'tpolygon' => 18, 'arbeth' => 18,
            'tarbeth' => 18, 'opeth' => 18, 'topeth' => 18, 'baseeth' => 18, 'tbaseeth' => 18, 'bsc' => 18,
            'tbsc' => 18, 'avaxc' => 18, 'tavaxc' => 18,
            'sol' => 9, 'tsol' => 9, 'xrp' => 6, 'txrp' => 6, 'xlm' => 7, 'txlm' => 7, 'trx' => 6, 'ttrx' => 6,
            'algo' => 6, 'talgo' => 6, 'ada' => 6, 'tada' => 6, 'dot' => 10, 'tdot' => 12, 'atom' => 6,
            'tatom' => 6, 'near' => 24, 'tnear' => 24, 'sui' => 9, 'tsui' => 9, 'apt' => 8, 'tapt' => 8,
            'ton' => 9, 'tton' => 9, 'hbar' => 8, 'thbar' => 8, 'xtz' => 6, 'txtz' => 6,
        ];

        $actual = [];
        foreach (CoinCode::cases() as $coin) {
            $actual[$coin->value] = $coin->decimals();
        }
        self::assertSame($expected, $actual);
    }

    public function testDecimalsWorkWithAmount(): void
    {
        self::assertSame('10000000000000000000', Amount::fromDisplay('10', CoinCode::HTETH->decimals())->baseUnits);
        self::assertSame('1', Amount::fromBaseUnits('1000000000000', CoinCode::TDOT->decimals())->toDisplay());
    }
}
