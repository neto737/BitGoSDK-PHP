<?php

namespace neto737\BitGoSDK\Resources\Wallet;

use neto737\BitGoSDK\Client;
use neto737\BitGoSDK\Enum\AddressTypes;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\AddressesList;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\Create;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\Deploy;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\ForwardTokens;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\Get;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\GetProof;
use neto737\BitGoSDK\Resources\Wallet\Commands\Address\Update;

class Address {

    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * List receive addresses on a wallet
     *
     * @param string|null $labelContains
     * @param int|null $limit
     * @param bool|null $mine
     * @param string|null $prevId
     * @param int|null $sort
     * @param int|null $chains
     * @param bool|null $includeBalances
     * @param bool|null $includeTotalAddressCount
     * @param string|null $returnBalancesForToken
     * @param bool|null $pendingDeployment
     * 
     * @return array
     */
    public function list(?string $labelContains = null, ?int $limit = null, ?bool $mine = null, ?string $prevId = null, ?int $sort = null, ?int $chains = null, ?bool $includeBalances = null, ?bool $includeTotalAddressCount = null, ?string $returnBalancesForToken = null, ?bool $pendingDeployment = null): array {
        return $this->client->send(new AddressesList($labelContains, $limit, $mine, $prevId, $sort, $chains, $includeBalances, $includeTotalAddressCount, $returnBalancesForToken, $pendingDeployment));
    }

    /**
     * This API call is used to create a new receive address for your wallet. You may choose to call this API whenever a deposit is made.
     *
     * @param int|null $chain
     * @param string|null $label
     * @param bool|null $lowPriority
     * @param int|null $gasPrice
     * @param int|null $forwarderVersion
     * @param string|null $onToken
     * @param string|null $format
     * 
     * @return array
     */
    public function create(?int $chain = AddressTypes::P2TR_DEPOSIT, ?string $label = null, ?bool $lowPriority = false, int $gasPrice = null, ?int $forwarderVersion = null, ?string $onToken = null, ?string $format = null): array {
        return $this->client->send(new Create($chain, $label, $lowPriority, $gasPrice, $forwarderVersion, $onToken, $format));
    }

    /**
     * This API call is to manually deploy an ETH address
     *
     * @param bool|null $forceDeploy
     * @param int|null $gasPrice
     * @param array|null $eip1559
     * 
     * @return array
     */
    public function deploy(?bool $forceDeploy = null, ?int $gasPrice = null, ?array $eip1559 = null): array {
        return $this->client->send(new Deploy($forceDeploy, $gasPrice, $eip1559));
    }

    /**
     * This API call is to manually forward tokens from an ETH or CELO address
     *
     * @param string|null $tokenName
     * @param bool|null $forceFlush
     * @param int|null $gasPrice
     * @param array|null $eip1559
     * 
     * @return array
     */
    public function forwardTokens(?string $tokenName = null, ?bool $forceFlush = null, ?int $gasPrice = null, ?array $eip1559): array {
        return $this->client->send(new ForwardTokens($tokenName, $forceFlush, $gasPrice, $eip1559));
    }

    /**
     * Gets a receive address on a wallet
     *
     * @param string|null $dt
     * @param string|null $memoId
     * 
     * @return array
     */
    public function get(?string $dt = null, ?string $memoId = null): array {
        return $this->client->send(new Get($dt, $memoId));
    }

    /**
     * Update a receive address on a wallet
     *
     * @param string|null $label
     * @param string|null $dt
     * @param string|null $memoId
     * 
     * @return array
     */
    public function update(?string $label = null, ?string $dt = null, ?string $memoId = null): array {
        return $this->client->send(new Update($label, $dt, $memoId));
    }

    /**
     * Gets proof of ownership for an address on a wallet
     *
     * @return array
     */
    public function getOwnershipProof(): array {
        return $this->client->send(new GetProof);
    }
}
