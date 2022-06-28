<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Address;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class Deploy extends Command {

    private $forceDeploy;
    private $gasPrice;
    private $eip1559;

    public function __construct(?bool $forceDeploy = null, ?int $gasPrice = null, ?array $eip1559 = null) {
        $this->forceDeploy  = $forceDeploy;
        $this->gasPrice     = $gasPrice;
        $this->eip1559      = $eip1559;
    }

    public function getRequestMethod(): string {
        return 'POST';
    }

    public function getEndpoint(): string {
        return '{coin}/wallet/{walletId}/address/{addressId}/deployment';
    }

    public function getBodyParameters(): array
    {
        return [
            'forceDeploy'   => $this->forceDeploy,
            'gasPrice'      => $this->gasPrice,
            'eip1559'       => $this->eip1559
        ];
    }
}
