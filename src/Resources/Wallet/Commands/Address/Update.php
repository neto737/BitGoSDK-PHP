<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Address;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class Update extends Command
{
    private $addressOrId;
    private $label;
    private $dt;
    private $memoId;

    public function __construct(string $addressOrId, ?string $label = null, ?string $dt = null, ?string $memoId = null)
    {
        $this->addressOrId  = $addressOrId;
        $this->label        = $label;
        $this->dt           = $dt;
        $this->memoId       = $memoId;
    }

    public function getRequestMethod(): string
    {
        return 'PUT';
    }

    public function getEndpoint(): string
    {
        return '{coin}/wallet/{walletId}/address/{addressOrId}';
    }

    public function getPathParam(): string
    {
        return $this->addressOrId;
    }

    public function getQueryParameters(): array
    {
        return [
            'dt'        => $this->dt,
            'memoId'    => $this->memoId
        ];
    }

    public function getBodyParameters(): array
    {
        return [
            'label'     => $this->label
        ];
    }
}
