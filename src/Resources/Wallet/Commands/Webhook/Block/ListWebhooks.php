<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Webhook\Block;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class ListWebhooks extends Command {

    public function getRequestMethod(): string {
        return 'GET';
    }

    public function getEndpoint(): string {
        return '{coin}/webhooks';
    }
}
