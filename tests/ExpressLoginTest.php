<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use PHPUnit\Framework\TestCase;

/**
 * B6: login() stores the returned token and only the login request itself goes without
 * an Authorization header. Every request sends Content-Type.
 */
final class ExpressLoginTest extends TestCase
{
    use AccessesPrivateMembers;

    public function testRequestWithoutTokenStillSendsContentType(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');

        self::assertSame(['Content-Type: application/json'], $this->headers($express));
    }

    public function testRequestSendsToken(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');
        $express->accessToken = 'v2xtoken';

        self::assertSame(['Content-Type: application/json', 'Authorization: Bearer v2xtoken'], $this->headers($express));
    }

    public function testLoginRequestSendsNoToken(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');
        $express->accessToken = 'v2xtoken';

        self::assertSame(['Content-Type: application/json'], $this->headers($express, false));
    }

    public function testCallsAfterLoginSendToken(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');
        $express->accessToken = 'v2xtoken';

        $express->login('user@example.com', 'password', '000000');

        self::assertSame(['Content-Type: application/json', 'Authorization: Bearer v2xtoken'], $this->headers($express));
    }

    public function testSuccessfulLoginStoresToken(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');

        $this->invoke($express, 'rememberAccessToken', ['access_token' => 'v2xnew', 'token_type' => 'bearer']);

        self::assertSame('v2xnew', $express->accessToken);
    }

    public function testFailedLoginKeepsToken(): void
    {
        $express = new BitGoExpress('127.0.0.1', 1, 'tbtc');
        $express->accessToken = 'v2xtoken';

        $this->invoke($express, 'rememberAccessToken', ['error' => 'needs a valid otp', 'status' => 401]);

        self::assertSame('v2xtoken', $express->accessToken);
    }

    /**
     * @return mixed
     */
    private function headers(BitGoExpress $express, bool $authenticate = true)
    {
        return $this->invoke($express, 'curlOptions', 'POST', [], $authenticate)[CURLOPT_HTTPHEADER];
    }
}
