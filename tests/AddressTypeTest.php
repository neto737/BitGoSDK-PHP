<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Enum\AddressType;
use PHPUnit\Framework\TestCase;
use ReflectionClassConstant;
use ReflectionParameter;

/**
 * B15: AddressType names match the script type of each BitGo chain, and the misleading P2SH_* names are deprecated.
 */
final class AddressTypeTest extends TestCase
{
    /**
     * @return array<string, array{int, int}>
     */
    public static function chains(): array
    {
        return [
            'P2SH' => [AddressType::LEGACY_DEPOSIT, 0],
            'P2SH change' => [AddressType::LEGACY_CHANGE, 1],
            'P2SH-P2WSH' => [AddressType::P2SH_P2WSH_DEPOSIT, 10],
            'P2SH-P2WSH change' => [AddressType::P2SH_P2WSH_CHANGE, 11],
            'P2WSH' => [AddressType::P2WSH_DEPOSIT, 20],
            'P2WSH change' => [AddressType::P2WSH_CHANGE, 21],
            'P2TR' => [AddressType::P2TR_DEPOSIT, 30],
            'P2TR change' => [AddressType::P2TR_CHANGE, 31],
            'bech32' => [AddressType::BECH32_DEPOSIT, 20],
            'bech32 change' => [AddressType::BECH32_CHANGE, 21],
            'bech32m' => [AddressType::BECH32M_DEPOSIT, 30],
            'bech32m change' => [AddressType::BECH32M_CHANGE, 31],
        ];
    }

    /**
     * @dataProvider chains
     */
    public function testChainValues(int $constant, int $chain): void
    {
        self::assertSame($chain, $constant);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function deprecatedConstants(): array
    {
        return [
            'P2SH_DEPOSIT' => ['P2SH_DEPOSIT', 'P2SH_P2WSH_DEPOSIT'],
            'P2SH_CHANGE' => ['P2SH_CHANGE', 'P2SH_P2WSH_CHANGE'],
        ];
    }

    /**
     * @dataProvider deprecatedConstants
     */
    public function testMisleadingNamesAreDeprecatedButKeepTheirValue(string $deprecated, string $replacement): void
    {
        $constant = new ReflectionClassConstant(AddressType::class, $deprecated);

        self::assertSame((new ReflectionClassConstant(AddressType::class, $replacement))->getValue(), $constant->getValue());
        self::assertMatchesRegularExpression('/@deprecated\s+2\.3\.0\b.*\b' . $replacement . '\b/', (string) $constant->getDocComment());
    }

    public function testCreateWalletAddressStillDefaultsToChain10(): void
    {
        $chain = new ReflectionParameter([BitGoSDK::class, 'createWalletAddress'], 'chain');

        self::assertSame(AddressType::P2SH_P2WSH_DEPOSIT, $chain->getDefaultValue());
        self::assertSame('neto737\BitGoSDK\Enum\AddressType::P2SH_P2WSH_DEPOSIT', $chain->getDefaultValueConstantName());
    }
}
