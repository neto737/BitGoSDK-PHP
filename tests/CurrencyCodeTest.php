<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\Enum\CurrencyCode;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionClassConstant;

/**
 * B16: CurrencyCode lists current BitGo coin codes, and outdated ones are deprecated rather than removed.
 */
final class CurrencyCodeTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function codes(): array
    {
        return [
            'Dogecoin' => [CurrencyCode::DOGECOIN, 'doge'],
            'Avalanche C-Chain' => [CurrencyCode::AVALANCHE_C_CHAIN, 'avaxc'],
            'Polygon' => [CurrencyCode::POLYGON, 'polygon'],
            'BNB Smart Chain' => [CurrencyCode::BNB_SMART_CHAIN, 'bsc'],
            'Solana testnet' => [CurrencyCode::SOLANA_TESTNET, 'tsol'],
            'Ethereum Hoodi testnet' => [CurrencyCode::ETHEREUM_HOODI_TESTNET, 'hteth'],
            'Ethereum Sepolia testnet' => [CurrencyCode::ETHEREUM_SEPOLIA_TESTNET, 'sepeth'],
            'Bitcoin testnet4' => [CurrencyCode::BITCOIN_TESTNET4, 'tbtc4'],
            'USD Coin' => [CurrencyCode::USD_COIN, 'usdc'],
            'Golem GLM' => [CurrencyCode::GOLEM_GLM, 'glm'],
        ];
    }

    /**
     * @dataProvider codes
     */
    public function testCodes(string $constant, string $code): void
    {
        self::assertSame($code, $constant);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function deprecatedConstants(): array
    {
        return [
            'AVAX' => ['AVAX', 'avax'],
            'AVAX_TESTNET' => ['AVAX_TESTNET', 'tavax'],
            'ETHEREUM_TESTNET' => ['ETHEREUM_TESTNET', 'teth'],
            'OMISE_GO' => ['OMISE_GO', 'omg'],
            'GOLEM' => ['GOLEM', 'gnt'],
            'AUGUR_REP' => ['AUGUR_REP', 'rep'],
            'KYBER_NETWORK' => ['KYBER_NETWORK', 'knc'],
            'QRL' => ['QRL', 'qrl'],
            'TENXPAY' => ['TENXPAY', 'pay'],
            'BREAD' => ['BREAD', 'brd'],
        ];
    }

    /**
     * @dataProvider deprecatedConstants
     */
    public function testOutdatedCodesAreDeprecatedButKept(string $name, string $code): void
    {
        $constant = new ReflectionClassConstant(CurrencyCode::class, $name);

        self::assertSame($code, $constant->getValue());
        self::assertMatchesRegularExpression('/@deprecated\s+2\.3\.0\b/', (string) $constant->getDocComment());
    }

    public function testDeprecationsNameAnExistingReplacement(): void
    {
        $class = new ReflectionClass(CurrencyCode::class);

        foreach ($class->getReflectionConstants() as $constant) {
            preg_match_all('/\b(?:Use|or) ([A-Z][A-Z0-9_]+)\b/', (string) $constant->getDocComment(), $matches);
            foreach ($matches[1] as $replacement) {
                self::assertTrue($class->hasConstant($replacement), $constant->getName() . ' points to missing ' . $replacement);
            }
        }
    }

    public function testCodesAreUnique(): void
    {
        $codes = (new ReflectionClass(CurrencyCode::class))->getConstants();

        self::assertSame(array_unique($codes), $codes);
    }
}
