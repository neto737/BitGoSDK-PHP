<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Tests\Fixtures\RecordingTransport;
use PHPUnit\Framework\TestCase;

/**
 * B6: login() stores the returned token and only the login request itself goes without
 * an Authorization header. Every request sends Content-Type.
 */
final class ExpressLoginTest extends TestCase
{
    private const USER_AGENT = 'User-Agent: BitGoSDK-PHP/' . BitGoSDK::VERSION;

    public function testRequestWithoutTokenStillSendsContentType(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $transport = RecordingTransport::on($express);

        $express->pingExpress();

        self::assertSame(['Content-Type: application/json', self::USER_AGENT], $transport->last()->headers);
    }

    public function testRequestSendsToken(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $express->accessToken = 'v2xtoken';
        $transport = RecordingTransport::on($express);

        $express->pingExpress();

        self::assertSame(['Content-Type: application/json', 'Authorization: Bearer v2xtoken', self::USER_AGENT], $transport->last()->headers);
    }

    public function testLoginRequestSendsNoToken(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $express->accessToken = 'v2xtoken';
        $transport = RecordingTransport::on($express);

        $express->login('user@example.com', 'password', '000000');

        self::assertSame(['Content-Type: application/json', self::USER_AGENT], $transport->last()->headers);
    }

    public function testSuccessfulLoginStoresToken(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $transport = RecordingTransport::on($express)->respond('{"access_token":"v2xnew","token_type":"bearer"}');

        $express->login('user@example.com', 'password', '000000');
        $express->pingExpress();

        self::assertSame('v2xnew', $express->accessToken);
        self::assertContains('Authorization: Bearer v2xnew', $transport->last()->headers);
    }

    public function testFailedLoginKeepsToken(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $express->accessToken = 'v2xtoken';
        $transport = RecordingTransport::on($express)->respond('{"error":"needs a valid otp","access_token":"v2xbogus"}', 401);

        $express->login('user@example.com', 'password', '000000');
        $express->pingExpress();

        self::assertSame('v2xtoken', $express->accessToken);
        self::assertContains('Authorization: Bearer v2xtoken', $transport->last()->headers);
    }

    public function testUnreachableLoginKeepsToken(): void
    {
        $express = new BitGoExpress('localhost', 3080, 'tbtc');
        $express->accessToken = 'v2xtoken';
        RecordingTransport::on($express)->fail(CURLE_COULDNT_CONNECT, 'Connection refused');

        $express->login('user@example.com', 'password', '000000');

        self::assertSame('v2xtoken', $express->accessToken);
    }
}
