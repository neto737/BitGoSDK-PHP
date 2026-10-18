<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;

/**
 * B3: amounts are int|string, sent as strings, and converted without float rounding.
 */
final class AmountTest extends TestCase
{
    use AccessesPrivateMembers;

    /**
     * @return array<string, array{string, int, string}>
     */
    public static function baseUnitCases(): array
    {
        return [
            '1.5 ETH' => ['1.5', 18, '1500000000000000000'],
            '10 ETH, above PHP_INT_MAX' => ['10', 18, '10000000000000000000'],
            '1 satoshi' => ['0.00000001', 8, '1'],
            'trailing zeros' => ['1.50000000000', 8, '150000000'],
            'leading zeros' => ['007', 2, '700'],
            'no integer part' => ['.5', 1, '5'],
            'zero' => ['0.0', 8, '0'],
            'negative' => ['-0.1', 8, '-10000000'],
            'no decimals' => ['42', 0, '42'],
        ];
    }

    /**
     * @dataProvider baseUnitCases
     */
    public function testToBaseUnits(string $amount, int $decimals, string $expected): void
    {
        self::assertSame($expected, BitGoSDK::toBaseUnits($amount, $decimals));
    }

    /**
     * @return array<string, array{int|string, int, string}>
     */
    public static function decimalCases(): array
    {
        return [
            '1.5 ETH' => ['1500000000000000000', 18, '1.500000000000000000'],
            '10 ETH, above PHP_INT_MAX' => ['10000000000000000000', 18, '10.000000000000000000'],
            '1 satoshi' => [1, 8, '0.00000001'],
            'zero' => [0, 8, '0.00000000'],
            'negative transfer value' => ['-150000000', 8, '-1.50000000'],
            'leading zeros' => ['00042', 0, '42'],
        ];
    }

    /**
     * @dataProvider decimalCases
     * @param int|string $amount
     */
    public function testFromBaseUnits($amount, int $decimals, string $expected): void
    {
        self::assertSame($expected, BitGoSDK::fromBaseUnits($amount, $decimals));
    }

    public function testToBaseUnitsRejectsExcessPrecision(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        BitGoSDK::toBaseUnits('0.000000001', 8);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidDecimals(): array
    {
        return [
            'empty' => [''],
            'dot only' => ['.'],
            'exponent' => ['1e5'],
            'comma' => ['1,5'],
            'text' => ['abc'],
        ];
    }

    /**
     * @dataProvider invalidDecimals
     */
    public function testToBaseUnitsRejectsNonDecimals(string $amount): void
    {
        $this->expectException(\InvalidArgumentException::class);
        BitGoSDK::toBaseUnits($amount, 8);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidIntegers(): array
    {
        return [
            'decimal string' => ['1.5'],
            'fractional float' => [1.5],
            'empty' => [''],
            'null' => [null],
        ];
    }

    /**
     * @dataProvider invalidIntegers
     * @param mixed $amount
     */
    public function testFromBaseUnitsRejectsNonIntegers($amount): void
    {
        $this->expectException(\InvalidArgumentException::class);
        BitGoSDK::fromBaseUnits($amount, 8);
    }

    public function testToSatoshiDoesNotDependOnLocale(): void
    {
        $previous = setlocale(LC_NUMERIC, '0');
        if (setlocale(LC_NUMERIC, 'de_DE.UTF-8', 'de_DE', 'German_Germany.1252', 'deu') === false) {
            self::markTestSkipped('No locale with a decimal comma is installed.');
        }

        try {
            self::assertSame(25000000, BitGoSDK::toSatoshi(0.25));
        } finally {
            setlocale(LC_NUMERIC, (string) $previous);
        }
    }

    public function testToSatoshiAcceptsStringsAndFloats(): void
    {
        self::assertSame(25000000, BitGoSDK::toSatoshi(0.25));
        self::assertSame(30000000, BitGoSDK::toSatoshi(0.1 + 0.2));
        self::assertSame(2100000000000000, BitGoSDK::toSatoshi('21000000'));
        self::assertSame(100000000, BitGoSDK::toSatoshi(1));
    }

    public function testToBtcIsExactAboveFloatPrecision(): void
    {
        self::assertSame('90071992.54740993', BitGoSDK::toBTC(9007199254740993));
        self::assertSame('0.00000001', BitGoSDK::toBTC(1));
        self::assertSame('1.00000000', BitGoSDK::toBTC('100000000'));
    }

    public function testExpressSendsRecipientAmountsAsStrings(): void
    {
        self::assertSame(
            [
                ['address' => 'a', 'amount' => '10000000000000000000'],
                ['address' => 'b', 'amount' => '1500'],
                ['address' => 'c'],
            ],
            $this->invoke(BitGoExpress::class, 'recipientAmountsToStrings', [
                ['address' => 'a', 'amount' => '10000000000000000000'],
                ['address' => 'b', 'amount' => 1500],
                ['address' => 'c'],
            ])
        );
    }

    public function testExpressRejectsDecimalAmounts(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');

        $this->expectException(\InvalidArgumentException::class);
        $express->sendTransaction('address', '0.5', 'passphrase');
    }
}
