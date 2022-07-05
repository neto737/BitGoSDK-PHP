<?php

namespace neto737\BitGoSDK\Resources\Wallet;

use neto737\BitGoSDK\Client;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet\Add as AddWalletWebhhok;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet\ListWebhooks as ListWalletWebhooks;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet\Remove as RemoveWalletWebhook;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet\Simulate as SimulateWalletWebhook;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block\Add as AddBlockWebhhok;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block\ListWebhooks as ListBlockWebhooks;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block\Remove as RemoveBlockWebhook;
use neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block\Simulate as SimulateBlockWebhook;

class Webhook {

    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * Add a webhook that will result in an HTTP callback at the specified URL from BitGo when events are triggered.
     *
     * @param string $type
     * @param string $url
     * @param string|null $label
     * @param int|null $numConfirmations
     * @param bool|null $allTokens
     * @param bool|null $listenToFailureStates
     * 
     * @return array
     */
    public function addWalletWebhhok(string $type, string $url, ?string $label = null, ?int $numConfirmations = null, ?bool $allTokens = null, ?bool $listenToFailureStates = null): array {
        return $this->client->send(new AddWalletWebhhok($type, $url, $label, $numConfirmations, $allTokens, $listenToFailureStates));
    }

    /**
     * List webhooks set up on the wallet.
     *
     * @return array
     */
    public function listWalletWebhooks(): array {
        return $this->client->send(new ListWalletWebhooks);
    }

    /**
     * Removing a webhook will cause new events of the specified type to no longer trigger HTTP callbacks to your URLs.
     *
     * @param string|null $id
     * @param string|null $type
     * @param string|null $url
     * 
     * @return array
     */
    public function removeWalletWebhook(?string $type = null, ?string $url = null, ?string $id = null): array {
        return $this->client->send(new RemoveWalletWebhook($type, $url, $id));
    }

    /**
     * Simulates and tests a webhook so you can view its response.
     *
     * @param string $webhookId
     * @param string|null $transferId
     * @param string|null $pendingApprovalId
     * 
     * @return array
     */
    public function simulateWalletWebhook(string $webhookId, ?string $transferId = null, ?string $pendingApprovalId = null): array {
        return $this->client->send(new SimulateWalletWebhook($webhookId, $transferId, $pendingApprovalId));
    }

    /**
     * Adds a webhook that will result in an HTTP callback at the specified URL from BitGo when events are triggered.
     *
     * @param string $type
     * @param string $url
     * @param string|null $label
     * @param int|null $numConfirmations
     * 
     * @return array
     */
    public function addBlockWebhhok(string $type, string $url, ?string $label = null, ?int $numConfirmations = null): array {
        return $this->client->send(new AddBlockWebhhok($type, $url, $label, $numConfirmations));
    }

    /**
     * Returns block webhooks.
     *
     * @return array
     */
    public function listBlockWebhooks(): array {
        return $this->client->send(new ListBlockWebhooks);
    }

    /**
     * Removing a webhook will cause new events of the specified type to no longer trigger HTTP callbacks to your URLs.
     *
     * @param string $type
     * @param string $url
     * @param string|null $id
     * 
     * @return array
     */
    public function removeBlockWebhook(string $type, string $url, ?string $id = null): array {
        return $this->client->send(new RemoveBlockWebhook($type, $url, $id));
    }

    /**
     * Simulates and tests a block webhook so you can view its response.
     *
     * @param string $webhookId
     * @param string|null $blockId
     * 
     * @return array
     */
    public function simulateBlockWebhook(string $webhookId, ?string $blockId = null): array {
        return $this->client->send(new SimulateBlockWebhook($webhookId, $blockId));
    }
}