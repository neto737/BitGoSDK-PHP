<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Address;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class GetProof extends Command {

    public function __construct() {
    }

    public function getRequestMethod(): string {
        return 'GET';
    }

    public function getEndpoint(): string {
        return '{coin}/wallet/{walletId}/address/{addressOrId}/proof';
    }
}
