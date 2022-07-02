<?php

use neto737\BitGoSDK\Authentication\Authentication;
use neto737\BitGoSDK\Authentication\Environment;
use neto737\BitGoSDK\Client;
use neto737\BitGoSDK\Enum\CurrencyCode;
use neto737\BitGoSDK\Enum\Environments;
use neto737\BitGoSDK\Resources\Wallet\Address;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\AddressesList;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\Create;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\Deploy;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\ForwardTokens;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\Get;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\GetProof;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\Update;
use PHPUnit\Framework\TestCase;

class addressTest extends TestCase {

    public function createClient(): Client {
        $auth = new Authentication('YOUR_API_KEY');
        $env = new Environment(Environments::TESTNET, '59cd72485007a239fb00282ed480da1f', CurrencyCode::BTC);

        return new Client($auth, $env);
    }

    public function createAddress(): Address {
        $client = $this->createClient();
        return $client->Address();
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Command
     */
    public function testCommand(): void {
        $command = $this->getMockForAbstractClass('\neto737\BitGoSDK\Resources\Wallet\Commands\Command');

        $this->assertSame('', $command->getPathParam());
        $this->assertIsArray($command->getQueryParameters());
        $this->assertIsArray($command->getBodyParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Authentication\Authentication
     * @covers \neto737\BitGoSDK\Authentication\Environment
     * @covers \neto737\BitGoSDK\Client
     * @covers \neto737\BitGoSDK\Resources\Wallet\Address
     */
    public function testAddress(): void {
        $client = $this->createClient();

        $this->assertSame(Address::class, get_class($client->Address()));
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\AddressesList
     */
    public function testAddressList(): void {
        $list = new AddressesList;

        $this->assertSame('GET', $list->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/addresses', $list->getEndpoint());
        $this->assertIsArray($list->getQueryParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\Create
     */
    public function testCreate(): void {
        $create = new Create;

        $this->assertSame('POST', $create->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/address', $create->getEndpoint());
        $this->assertIsArray($create->getBodyParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\Deploy
     */
    public function testDeploy(): void {
        $deploy = new Deploy('ADDRESS_ID');

        $this->assertSame('POST', $deploy->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/address/{addressId}/deployment', $deploy->getEndpoint());
        $this->assertSame('ADDRESS_ID', $deploy->getPathParam());
        $this->assertIsArray($deploy->getBodyParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\ForwardTokens
     */
    public function testForwardTokens(): void {
        $forward = new ForwardTokens('ADDRESS_ID', 'Token', false, 0, []);

        $this->assertSame('POST', $forward->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/address/{addressId}/tokenforward', $forward->getEndpoint());
        $this->assertSame('ADDRESS_ID', $forward->getPathParam());
        $this->assertIsArray($forward->getBodyParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\Get
     */
    public function testGet(): void {
        $get = new Get('ADDRESS_OR_ID');

        $this->assertSame('GET', $get->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/address/{addressOrId}', $get->getEndpoint());
        $this->assertSame('ADDRESS_OR_ID', $get->getPathParam());
        $this->assertIsArray($get->getQueryParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\GetProof
     */
    public function testGetProof(): void {
        $proof = new GetProof('ADDRESS_OR_ID');

        $this->assertSame('GET', $proof->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/address/{addressOrId}/proof', $proof->getEndpoint());
        $this->assertSame('ADDRESS_OR_ID', $proof->getPathParam());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\Update
     */
    public function testUpdate(): void {
        $update = new Update('ADDRESS_OR_ID');

        $this->assertSame('PUT', $update->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/address/{addressOrId}', $update->getEndpoint());
        $this->assertSame('ADDRESS_OR_ID', $update->getPathParam());
        $this->assertIsArray($update->getQueryParameters());
        $this->assertIsArray($update->getBodyParameters());
    }
}