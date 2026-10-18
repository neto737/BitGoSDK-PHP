<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use PHPUnit\Framework\TestCase;

/**
 * B7: Express can be reached over https://, with a custom CA for self-signed certificates.
 */
final class ExpressTlsTest extends TestCase
{
    use AccessesPrivateMembers;

    public function testBareHostnameUsesHttp(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');

        self::assertSame('http://localhost:3080/api/v2', $this->getPrivate($express, 'AuthAPIEndpoint'));
        self::assertSame('http://localhost:3080/api/v2/tbtc', $this->getPrivate($express, 'APIEndpoint'));
        self::assertSame('localhost', $express->hostname);
    }

    public function testHttpsPrefixUsesHttps(): void
    {
        $express = new BitGoExpress('https://express.internal/', 3080, 'tbtc');

        self::assertSame('https://express.internal:3080/api/v2', $this->getPrivate($express, 'AuthAPIEndpoint'));
        self::assertSame('https://express.internal:3080/api/v2/tbtc', $this->getPrivate($express, 'APIEndpoint'));
        self::assertSame('express.internal', $express->hostname);
    }

    public function testSchemeIsCaseInsensitive(): void
    {
        $express = new BitGoExpress('HTTPS://express.internal', 3080, 'tbtc');

        self::assertSame('https://express.internal:3080/api/v2', $this->getPrivate($express, 'AuthAPIEndpoint'));
    }

    public function testOtherSchemesAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new BitGoExpress('ftp://express.internal', 3080, 'tbtc');
    }

    public function testCertificatesAreVerifiedByDefault(): void
    {
        $options = $this->curlOptions(new BitGoExpress('https://express.internal', 3080, 'tbtc'));

        self::assertTrue($options[CURLOPT_SSL_VERIFYPEER]);
        self::assertSame(2, $options[CURLOPT_SSL_VERIFYHOST]);
    }

    public function testCustomCaFileIsUsed(): void
    {
        $express = new BitGoExpress('https://express.internal', 3080, 'tbtc');

        $express->setCaFile(__FILE__);

        self::assertSame(__FILE__, $this->curlOptions($express)[CURLOPT_CAINFO]);
    }

    public function testMissingCaFileIsRejected(): void
    {
        $express = new BitGoExpress('https://express.internal', 3080, 'tbtc');

        $this->expectException(\InvalidArgumentException::class);
        $express->setCaFile(__DIR__ . '/does-not-exist.pem');
    }

    public function testVerificationCanBeTurnedOff(): void
    {
        $express = new BitGoExpress('https://express.internal', 3080, 'tbtc');

        $express->setVerifyTls(false);
        $options = $this->curlOptions($express);

        self::assertFalse($options[CURLOPT_SSL_VERIFYPEER]);
        self::assertSame(0, $options[CURLOPT_SSL_VERIFYHOST]);
    }

    /**
     * @return array<int, mixed>
     */
    private function curlOptions(BitGoExpress $express): array
    {
        return $this->invoke($express, 'curlOptions', 'POST', []);
    }
}
