<?php

use neto737\BitGoSDK\Authentication\Authentication;
use neto737\BitGoSDK\Authentication\Environment;
use neto737\BitGoSDK\Client;
use neto737\BitGoSDK\Enum\Currencies\Testnet;
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

class addressTest extends TestCase
{
    public function createClient(): Client
    {
        $auth = new Authentication('YOUR_API_KEY');
        $env = new Environment(Environments::TESTNET, '59cd72485007a239fb00282ed480da1f', Testnet::BTC);

        return new Client($auth, $env);
    }

    public function createAddress(): Address
    {
        $client = $this->createClient();
        return $client->Address();
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Command
     */
    public function testCommand(): void
    {
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
    public function testAddress(): void
    {
        $client = $this->createClient();

        $this->assertSame(Address::class, get_class($client->Address()));
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\AddressesList
     */
    public function testAddressList(): void
    {
        $command = new AddressesList;

        $this->assertSame('GET', $command->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/addresses', $command->getEndpoint());
        $this->assertIsArray($command->getQueryParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\Create
     */
    public function testCreate(): void
    {
        $command = new Create;

        $this->assertSame('POST', $command->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/address', $command->getEndpoint());
        $this->assertIsArray($command->getBodyParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\Deploy
     */
    public function testDeploy(): void
    {
        $command = new Deploy('ADDRESS_ID');

        $this->assertSame('POST', $command->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/address/{addressId}/deployment', $command->getEndpoint());
        $this->assertSame('ADDRESS_ID', $command->getPathParam());
        $this->assertIsArray($command->getBodyParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\ForwardTokens
     */
    public function testForwardTokens(): void
    {
        $command = new ForwardTokens('ADDRESS_ID', 'Token', false, 0, []);

        $this->assertSame('POST', $command->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/address/{addressId}/tokenforward', $command->getEndpoint());
        $this->assertSame('ADDRESS_ID', $command->getPathParam());
        $this->assertIsArray($command->getBodyParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\Get
     */
    public function testGet(): void
    {
        $command = new Get('ADDRESS_OR_ID');

        $this->assertSame('GET', $command->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/address/{addressOrId}', $command->getEndpoint());
        $this->assertSame('ADDRESS_OR_ID', $command->getPathParam());
        $this->assertIsArray($command->getQueryParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\GetProof
     */
    public function testGetProof(): void
    {
        $command = new GetProof('ADDRESS_OR_ID');

        $this->assertSame('GET', $command->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/address/{addressOrId}/proof', $command->getEndpoint());
        $this->assertSame('ADDRESS_OR_ID', $command->getPathParam());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Address\Update
     */
    public function testUpdate(): void
    {
        $command = new Update('ADDRESS_OR_ID');

        $this->assertSame('PUT', $command->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/address/{addressOrId}', $command->getEndpoint());
        $this->assertSame('ADDRESS_OR_ID', $command->getPathParam());
        $this->assertIsArray($command->getQueryParameters());
        $this->assertIsArray($command->getBodyParameters());
    }
}
