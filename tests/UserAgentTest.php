<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;

/**
 * Both clients identify themselves with a User-Agent header.
 */
final class UserAgentTest extends TestCase
{
    use AccessesPrivateMembers;

    public function testVersionIsSemver(): void
    {
        self::assertMatchesRegularExpression('/^\d+\.\d+\.\d+(-[0-9A-Za-z.]+)?$/', BitGoSDK::VERSION);
    }

    /**
     * @dataProvider clients
     * @param BitGoSDK|BitGoExpress $client
     */
    public function testUserAgentIsSent($client): void
    {
        $headers = $this->invoke($client, 'curlOptions', 'POST', [])[CURLOPT_HTTPHEADER];

        self::assertContains('User-Agent: BitGoSDK-PHP/' . BitGoSDK::VERSION, $headers);
    }

    public function testUserAgentIsSentWithoutAuthentication(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');

        $headers = $this->invoke($express, 'curlOptions', 'POST', [], false)[CURLOPT_HTTPHEADER];

        self::assertContains('User-Agent: BitGoSDK-PHP/' . BitGoSDK::VERSION, $headers);
    }

    /**
     * @return array<string, array{BitGoSDK|BitGoExpress}>
     */
    public function clients(): array
    {
        return [
            'BitGoSDK' => [new BitGoSDK('token', 'tbtc', true)],
            'BitGoExpress' => [new BitGoExpress('localhost', 3080, 'tbtc')],
        ];
    }
}
