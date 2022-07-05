<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Wallet;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class Remove extends Command {

    private $type;
    private $url;
    private $id;

    public function __construct(?string $type = null, ?string $url = null, ?string $id = null) {
        $this->type = $type;
        $this->url  = $url;
        $this->id   = $id;
    }

    public function getRequestMethod(): string {
        return 'DELETE';
    }

    public function getEndpoint(): string {
        return '{coin}/wallet/{walletId}/webhooks';
    }

    public function getBodyParameters(): array {
        return [
            'type'  => $this->type,
            'url'   => $this->url,
            'id'    => $this->id
        ];
    }
}