<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\Amount;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

final class AmountTest extends TestCase
{
    public function testTenEthRoundTripsWithoutPrecisionLoss(): void
    {
        $amount = Amount::fromDisplay('10', 18);

        self::assertSame('10000000000000000000', $amount->baseUnits);
        self::assertSame('10', Amount::fromBaseUnits('10000000000000000000', 18)->toDisplay());
    }

    /**
     * @return array<string, array{string, int, string, string}>
     */
    public static function conversions(): array
    {
        return [
            'half a bitcoin' => ['0.5', 8, '50000000', '0.5'],
            'one satoshi' => ['0.00000001', 8, '1', '0.00000001'],
            'one wei' => ['0.000000000000000001', 18, '1', '0.000000000000000001'],
            'more than PHP_INT_MAX' => ['123456789.123456789123456789', 18, '123456789123456789123456789', '123456789.123456789123456789'],
            'USDC' => ['1250.75', 6, '1250750000', '1250.75'],
            'whole number' => ['21', 8, '2100000000', '21'],
            'negative' => ['-0.0012', 8, '-120000', '-0.0012'],
            'zero' => ['0', 8, '0', '0'],
            'negative zero' => ['-0.0', 8, '0', '0'],
            'no decimals' => ['42', 0, '42', '42'],
            'trailing zeros past the decimals' => ['1.50000000000', 8, '150000000', '1.5'],
            'leading zeros' => ['007.5', 1, '75', '7.5'],
            'no whole part' => ['.5', 8, '50000000', '0.5'],
            'no fraction' => ['5.', 8, '500000000', '5'],
        ];
    }

    #[DataProvider('conversions')]
    public function testConvertsBothWays(string $display, int $decimals, string $baseUnits, string $displayed): void
    {
        $amount = Amount::fromDisplay($display, $decimals);

        self::assertSame($baseUnits, $amount->baseUnits);
        self::assertSame($decimals, $amount->decimals);
        self::assertSame($displayed, $amount->toDisplay());
        self::assertSame($displayed, Amount::fromBaseUnits($baseUnits, $decimals)->toDisplay());
    }

    public function testBaseUnitsAcceptAnInt(): void
    {
        self::assertSame('0.5', Amount::fromBaseUnits(50000000, 8)->toDisplay());
        self::assertSame('-1', Amount::fromBaseUnits(-100000000, 8)->toDisplay());
    }

    public function testBaseUnitsAreNormalized(): void
    {
        self::assertSame('5', Amount::fromBaseUnits('0005', 8)->baseUnits);
        self::assertSame('0', Amount::fromBaseUnits('-000', 8)->baseUnits);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function badDisplayAmounts(): array
    {
        return [
            'empty' => [''],
            'only a point' => ['.'],
            'only a sign' => ['-'],
            'exponent' => ['1e18'],
            'thousands separator' => ['1,000'],
            'comma decimal' => ['0,5'],
            'plus sign' => ['+1'],
            'spaces' => [' 1'],
            'letters' => ['ten'],
        ];
    }

    #[DataProvider('badDisplayAmounts')]
    public function testRejectsADisplayAmountThatIsNotADecimal(string $display): void
    {
        $this->expectException(InvalidArgumentException::class);
        Amount::fromDisplay($display, 8);
    }

    public function testRejectsMoreDecimalPlacesThanTheCoinHas(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('more than 8 decimal places');
        Amount::fromDisplay('0.000000001', 8);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function badBaseUnits(): array
    {
        return ['empty' => [''], 'decimal' => ['1.5'], 'exponent' => ['1e18'], 'only a sign' => ['-']];
    }

    #[DataProvider('badBaseUnits')]
    public function testRejectsBaseUnitsThatAreNotAWholeNumber(string $baseUnits): void
    {
        $this->expectException(InvalidArgumentException::class);
        Amount::fromBaseUnits($baseUnits, 8);
    }

    public function testRejectsNegativeDecimals(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Amount::fromDisplay('1', -1);
    }

    public function testRejectsNegativeDecimalsForBaseUnits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Amount::fromBaseUnits('1', -1);
    }

    public function testIsSentAsItsBaseUnitsInABody(): void
    {
        $this->http()->post('{coin}/wallet/{walletId}/tx/send', ['coin' => 'hteth', 'walletId' => 'w1'], extra: ['amount' => Amount::fromDisplay('10', 18)]);

        self::assertSame('{"amount":"10000000000000000000"}', (string) $this->lastRequest()->getBody());
    }

    public function testIsSentAsItsBaseUnitsInAQuery(): void
    {
        $this->http()->get('{coin}/wallet/{walletId}', ['coin' => 'tbtc', 'walletId' => 'w1'], extra: ['minValue' => Amount::fromDisplay('0.5', 8)]);

        self::assertSame(self::API . 'tbtc/wallet/w1?minValue=50000000', (string) $this->lastRequest()->getUri());
    }

    public function testIsAStringOfItsBaseUnits(): void
    {
        self::assertSame('50000000', (string) Amount::fromDisplay('0.5', 8));
    }
}
