<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class Simulate extends Command
{
    private $webhookId;
    private $blockId;

    public function __construct(string $webhookId, ?string $blockId = null)
    {
        $this->webhookId    = $webhookId;
        $this->blockId      = $blockId;
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    public function getEndpoint(): string
    {
        return '{coin}/webhooks/{webhookId}/simulate';
    }

    public function getPathParam(): string
    {
        return $this->webhookId;
    }

    public function getBodyParameters(): array
    {
        return [
            'blockId' => $this->blockId
        ];
    }
}
