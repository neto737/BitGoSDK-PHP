<?php

use neto737\BitGoSDK\Authentication\Authentication;
use neto737\BitGoSDK\Authentication\Environment;
use neto737\BitGoSDK\Client;
use neto737\BitGoSDK\Enum\CurrencyCode;
use neto737\BitGoSDK\Enum\Environments;
use neto737\BitGoSDK\Resources\Wallet\Address;
use PHPUnit\Framework\TestCase;

class clientTest extends TestCase {

    /**
     * @covers \neto737\BitGoSDK\Authentication\Authentication
     */
    public function testAuthentication(): void {
        $auth = new Authentication('YOUR_API_KEY');

        $this->assertSame('YOUR_API_KEY', $auth->getApiKey());

        $auth->setApiKey('YOUR_SECOND_API_KEY');

        $this->assertSame('YOUR_SECOND_API_KEY', $auth->getApiKey());
    }

    /**
     * @covers \neto737\BitGoSDK\Authentication\Environment
     */
    public function testEnvironment(): void {
        $env = new Environment(Environments::MAINNET, '59cd72485007a239fb00282ed480da1f', CurrencyCode::BTC);

        $this->assertSame('https://app.bitgo.com/api/v2/', $env->getEnvironment());
        $this->assertSame('59cd72485007a239fb00282ed480da1f', $env->getWalletId());
        $this->assertSame('BTC', $env->getCurrency());
    }

    /**
     * @covers \neto737\BitGoSDK\Authentication\Environment
     */
    public function testEnvironmentInvalidWalletId(): void {
        $this->expectExceptionMessage('Invalid wallet id format.');

        $env = new Environment(Environments::MAINNET, 'YOUR_WALLET_ID', CurrencyCode::BTC);
    }

    /**
     * @covers \neto737\BitGoSDK\Authentication\Authentication
     * @covers \neto737\BitGoSDK\Authentication\Environment
     * @covers \neto737\BitGoSDK\Client
     * @covers \neto737\BitGoSDK\Resources\Wallet\Address
     */
    public function testAddress(): void {
        $auth = new Authentication('YOUR_API_KEY');
        $env = new Environment(Environments::TESTNET, '59cd72485007a239fb00282ed480da1f', CurrencyCode::BTC);

        $client = new Client($auth, $env);
        $address = $client->Address();
        
        $this->assertEquals(new Address($client), $address);
    }
}