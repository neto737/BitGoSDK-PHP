<?php

namespace neto737\BitGoSDK\Tests;

use Composer\CaBundle\CaBundle;
use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;

/**
 * Both clients trust the system CA store by default, falling back to composer/ca-bundle, and accept a custom CA file.
 */
final class CaBundleTest extends TestCase
{
    use AccessesPrivateMembers;

    public function testBundledCacertIsGone(): void
    {
        self::assertFileDoesNotExist(dirname(__DIR__) . '/src/cacert.pem');
    }

    /**
     * @dataProvider clients
     * @param BitGoSDK|BitGoExpress $client
     */
    public function testSystemCaStoreIsUsedByDefault($client): void
    {
        $expected = CaBundle::getSystemCaRootBundlePath();
        $options = $this->curlOptions($client);

        self::assertSame($expected, $options[is_dir($expected) ? CURLOPT_CAPATH : CURLOPT_CAINFO]);
        self::assertTrue($options[CURLOPT_SSL_VERIFYPEER]);
    }

    /**
     * @dataProvider clients
     * @param BitGoSDK|BitGoExpress $client
     */
    public function testCustomCaFileIsUsed($client): void
    {
        $client->setCaFile(__FILE__);
        $options = $this->curlOptions($client);

        self::assertSame(__FILE__, $options[CURLOPT_CAINFO]);
        self::assertArrayNotHasKey(CURLOPT_CAPATH, $options);
    }

    /**
     * @dataProvider clients
     * @param BitGoSDK|BitGoExpress $client
     */
    public function testMissingCaFileIsRejected($client): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $client->setCaFile(__DIR__ . '/does-not-exist.pem');
    }

    /**
     * @return array<string, array{BitGoSDK|BitGoExpress}>
     */
    public function clients(): array
    {
        return [
            'BitGoSDK' => [new BitGoSDK('token', 'tbtc', true)],
            'BitGoExpress' => [new BitGoExpress('https://express.internal', 3080, 'tbtc')],
        ];
    }

    /**
     * @param BitGoSDK|BitGoExpress $client
     * @return array<int, mixed>
     */
    private function curlOptions($client): array
    {
        return $this->invoke($client, 'curlOptions', 'POST', []);
    }
}
