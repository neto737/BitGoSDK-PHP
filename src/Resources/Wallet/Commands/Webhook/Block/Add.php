<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class Add extends Command
{
    private $type;
    private $url;
    private $label;
    private $numConfirmations;

    public function __construct(string $type, string $url, ?string $label = null, ?int $numConfirmations = null)
    {
        $this->type                     = $type;
        $this->url                      = $url;
        $this->label                    = $label;
        $this->numConfirmations         = $numConfirmations;
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    public function getEndpoint(): string
    {
        return '{coin}/webhooks';
    }

    public function getBodyParameters(): array
    {
        return [
            'type'                  => $this->type,
            'url'                   => $this->url,
            'label'                 => $this->label,
            'numConfirmations'      => $this->numConfirmations
        ];
    }
}
