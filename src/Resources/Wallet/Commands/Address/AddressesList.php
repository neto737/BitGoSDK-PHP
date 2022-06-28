<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Address;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class AddressesList extends Command {

    private $labelContains;
    private $limit;
    private $mine;
    private $prevId;
    private $sort;
    private $chains;
    private $includeBalances;
    private $includeTotalAddressCount;
    private $returnBalancesForToken;
    private $pendingDeployment;

    public function __construct(
        ?string $labelContains = null,
        ?int $limit = null,
        ?bool $mine = null,
        ?string $prevId = null,
        ?int $sort = null,
        ?int $chains = null,
        ?bool $includeBalances = null,
        ?bool $includeTotalAddressCount = null,
        ?string $returnBalancesForToken = null,
        ?bool $pendingDeployment = null
    ) {
        $this->labelContains            = $labelContains;
        $this->limit                    = $limit;
        $this->mine                     = $mine;
        $this->prevId                   = $prevId;
        $this->sort                     = $sort;
        $this->chains                   = $chains;
        $this->includeBalances          = $includeBalances;
        $this->includeTotalAddressCount = $includeTotalAddressCount;
        $this->returnBalancesForToken   = $returnBalancesForToken;
        $this->pendingDeployment        = $pendingDeployment;
    }

    public function getRequestMethod(): string {
        return 'GET';
    }

    public function getEndpoint(): string {
        return '{coin}/wallet/{walletId}/addresses';
    }

    public function getQueryParameters(): array {
        return [
            'labelContains'             => $this->labelContains,
            'limit'                     => $this->limit,
            'mine'                      => $this->mine,
            'prevId'                    => $this->prevId,
            'sort'                      => $this->sort,
            'chains'                    => $this->chains,
            'includeBalances'           => $this->includeBalances,
            'includeTotalAddressCount'  => $this->includeTotalAddressCount,
            'returnBalancesForToken'    => $this->returnBalancesForToken,
            'pendingDeployment'         => $this->pendingDeployment
        ];
    }
}
