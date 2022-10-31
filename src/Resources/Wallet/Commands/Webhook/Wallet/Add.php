<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class Add extends Command
{
    private $type;
    private $url;
    private $label;
    private $numConfirmations;
    private $allTokens;
    private $listenToFailureStates;

    public function __construct(string $type, string $url, ?string $label = null, ?int $numConfirmations = null, ?bool $allTokens = null, ?bool $listenToFailureStates = null)
    {
        $this->type                     = $type;
        $this->url                      = $url;
        $this->label                    = $label;
        $this->numConfirmations         = $numConfirmations;
        $this->allTokens                = $allTokens;
        $this->listenToFailureStates    = $listenToFailureStates;
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    public function getEndpoint(): string
    {
        return '{coin}/wallet/{walletId}/webhooks';
    }

    public function getBodyParameters(): array
    {
        return [
            'type'                  => $this->type,
            'url'                   => $this->url,
            'label'                 => $this->label,
            'numConfirmations'      => $this->numConfirmations,
            'allTokens'             => $this->allTokens,
            'listenToFailureStates' => $this->listenToFailureStates
        ];
    }
}
