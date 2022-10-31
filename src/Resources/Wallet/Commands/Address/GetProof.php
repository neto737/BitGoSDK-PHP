<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Address;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class GetProof extends Command
{
    private $addressOrId;

    public function __construct(string $addressOrId)
    {
        $this->addressOrId = $addressOrId;
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }

    public function getPathParam(): string
    {
        return $this->addressOrId;
    }

    public function getEndpoint(): string
    {
        return '{coin}/wallet/{walletId}/address/{addressOrId}/proof';
    }
}
