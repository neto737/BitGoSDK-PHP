<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\Config;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testProductionAndTestnetUrls(): void
    {
        self::assertSame('https://app.bitgo.com/api/v2/', Config::production('t')->baseUrl);
        self::assertSame('https://app.bitgo-test.com/api/v2/', Config::testnet('t')->baseUrl);
        self::assertSame('https://app.bitgo.com/api/v2/', (new Config('t'))->baseUrl);
    }

    public function testCustomBaseUrlEndsInOneSlash(): void
    {
        self::assertSame('https://proxy.example.com/bitgo/', (new Config('t', 'https://proxy.example.com/bitgo'))->baseUrl);
        self::assertSame('https://proxy.example.com/bitgo/', (new Config('t', 'https://proxy.example.com/bitgo//'))->baseUrl);
    }

    public function testEmptyAccessTokenIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config('');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function badUrls(): array
    {
        return [
            'plain http' => ['http://app.bitgo.com/api/v2/'],
            'no scheme' => ['app.bitgo.com/api/v2/'],
            'empty' => [''],
        ];
    }

    #[DataProvider('badUrls')]
    public function testBaseUrlMustBeHttps(string $url): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config('t', $url);
    }

    public function testDumpsHideTheAccessToken(): void
    {
        $config = Config::testnet('secret-token');

        self::assertStringNotContainsString('secret-token', print_r($config, true));
        ob_start();
        var_dump($config);
        self::assertStringNotContainsString('secret-token', (string) ob_get_clean());
    }
}
