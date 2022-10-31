<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class Simulate extends Command
{
    private $webhookId;
    private $transferId;
    private $pendingApprovalId;

    public function __construct(string $webhookId, ?string $transferId = null, ?string $pendingApprovalId = null)
    {
        $this->webhookId            = $webhookId;
        $this->transferId           = $transferId;
        $this->pendingApprovalId    = $pendingApprovalId;
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    public function getEndpoint(): string
    {
        return '{coin}/wallet/{walletId}/webhooks/{webhookId}/simulate';
    }

    public function getPathParam(): string
    {
        return $this->webhookId;
    }

    public function getBodyParameters(): array
    {
        return [
            'transferId'        => $this->transferId,
            'pendingApprovalId' => $this->pendingApprovalId
        ];
    }
}
