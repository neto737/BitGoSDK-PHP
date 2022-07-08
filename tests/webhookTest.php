<?php

use neto737\BitGoSDK\Authentication\Authentication;
use neto737\BitGoSDK\Authentication\Environment;
use neto737\BitGoSDK\Client;
use neto737\BitGoSDK\Enum\Currencies\Testnet;
use neto737\BitGoSDK\Enum\Environments;
use neto737\BitGoSDK\Resources\Wallet\Webhook;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet\Add as AddWalletWebhhok;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet\ListWebhooks as ListWalletWebhooks;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet\Remove as RemoveWalletWebhook;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet\Simulate as SimulateWalletWebhook;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block\Add as AddBlockWebhhok;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block\ListWebhooks as ListBlockWebhooks;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block\Remove as RemoveBlockWebhook;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block\Simulate as SimulateBlockWebhook;
use PHPUnit\Framework\TestCase;

class webhookTest extends TestCase {

    public function createClient(): Client {
        $auth = new Authentication('YOUR_API_KEY');
        $env = new Environment(Environments::TESTNET, '59cd72485007a239fb00282ed480da1f', Testnet::BTC);

        return new Client($auth, $env);
    }

    /**
     * @covers \neto737\BitGoSDK\Authentication\Authentication
     * @covers \neto737\BitGoSDK\Authentication\Environment
     * @covers \neto737\BitGoSDK\Client
     * @covers \neto737\BitGoSDK\Resources\Wallet\Webhook
     */
    public function testWebhook(): void {
        $client = $this->createClient();

        $this->assertSame(Webhook::class, get_class($client->Webhook()));
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet\Add
     */
    public function testAddWalletWebhook(): void {
        $command = new AddWalletWebhhok('transfer', 'https://example.com/webhook');

        $this->assertSame('POST', $command->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/webhooks', $command->getEndpoint());
        $this->assertIsArray($command->getBodyParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet\ListWebhooks
     */
    public function testListWalletWebhooks(): void {
        $command = new ListWalletWebhooks;

        $this->assertSame('GET', $command->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/webhooks', $command->getEndpoint());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet\Remove
     */
    public function testRemoveWalletWebhook(): void {
        $command = new RemoveWalletWebhook;

        $this->assertSame('DELETE', $command->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/webhooks', $command->getEndpoint());
        $this->assertIsArray($command->getBodyParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet\Simulate
     */
    public function testSimulateWalletWebhook(): void {
        $command = new SimulateWalletWebhook('59cd72485007a239fb00282ed480da1f');

        $this->assertSame('POST', $command->getRequestMethod());
        $this->assertSame('{coin}/wallet/{walletId}/webhooks/{webhookId}/simulate', $command->getEndpoint());
        $this->assertSame('59cd72485007a239fb00282ed480da1f', $command->getPathParam());
        $this->assertIsArray($command->getBodyParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block\Add
     */
    public function testAddBlockWebhook(): void {
        $command = new AddBlockWebhhok('block', 'https://example.com/webhook');

        $this->assertSame('POST', $command->getRequestMethod());
        $this->assertSame('{coin}/webhooks', $command->getEndpoint());
        $this->assertIsArray($command->getBodyParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block\ListWebhooks
     */
    public function testListBlockWebhooks(): void {
        $command = new ListBlockWebhooks;

        $this->assertSame('GET', $command->getRequestMethod());
        $this->assertSame('{coin}/webhooks', $command->getEndpoint());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block\Remove
     */
    public function testRemoveBlockWebhook(): void {
        $command = new RemoveBlockWebhook('block', 'https://example.com/webhook');

        $this->assertSame('DELETE', $command->getRequestMethod());
        $this->assertSame('{coin}/webhooks', $command->getEndpoint());
        $this->assertIsArray($command->getBodyParameters());
    }

    /**
     * @covers \neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block\Simulate
     */
    public function testSimulateBlockWebhook(): void {
        $command = new SimulateBlockWebhook('59cd72485007a239fb00282ed480da1f');

        $this->assertSame('POST', $command->getRequestMethod());
        $this->assertSame('{coin}/webhooks/{webhookId}/simulate', $command->getEndpoint());
        $this->assertSame('59cd72485007a239fb00282ed480da1f', $command->getPathParam());
        $this->assertIsArray($command->getBodyParameters());
    }
}