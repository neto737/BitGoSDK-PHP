<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Address;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class Get extends Command {

    private $dt;
    private $memoId;

    public function __construct(?string $dt = null, ?string $memoId = null) {
        $this->dt       = $dt;
        $this->memoId   = $memoId;
    }

    public function getRequestMethod(): string {
        return 'GET';
    }

    public function getEndpoint(): string {
        return '{coin}/wallet/{walletId}/address/{addressOrId}';
    }

    public function getQueryParameters(): array {
        return [
            'dt'        => $this->dt,
            'memoId'    => $this->memoId
        ];
    }
}
