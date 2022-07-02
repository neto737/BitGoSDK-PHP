<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Address;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class ForwardTokens extends Command {

    private $addressId;
    private $tokenName;
    private $forceFlush;
    private $gasPrice;
    private $eip1559;

    public function __construct(string $addressId, ?string $tokenName = null, ?bool $forceFlush = null, ?int $gasPrice = null, ?array $eip1559) {
        $this->addressId    = $addressId;
        $this->tokenName    = $tokenName;
        $this->forceFlush   = $forceFlush;
        $this->gasPrice     = $gasPrice;
        $this->eip1559      = $eip1559;
    }

    public function getRequestMethod(): string {
        return 'POST';
    }

    public function getEndpoint(): string {
        return '{coin}/wallet/{walletId}/address/{addressId}/tokenforward';
    }

    public function getPathParam(): string {
        return $this->addressId;
    }

    public function getBodyParameters(): array {
        return [
            'tokenName'     => $this->tokenName,
            'forceFlush'    => $this->forceFlush,
            'gasPrice'      => $this->gasPrice,
            'eip1559'       => $this->eip1559
        ];
    }
}
