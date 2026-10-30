<?php

/**
 *  ____  _ _    ____      ____  ____  _  __
 * | __ )(_) |_ / ___| ___/ ___||  _ \| |/ /
 * |  _ \| | __| |  _ / _ \___ \| | | | ' /
 * | |_) | | |_| |_| | (_) |__) | |_| | . \
 * |____/|_|\__|\____|\___/____/|____/|_|\_\
 *
 * @package BitGoSDK PHP
 * @author  Neto Melo <neto737@live.com>
 * @license https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace neto737\BitGoSDK\Wallet;

use neto737\BitGoSDK\Exception\BitGoException;
use neto737\BitGoSDK\Internal\Http;
use neto737\BitGoSDK\Paginator;

/**
 * A wallet's receive addresses: /api/v2/{coin}/wallet/{walletId}/address.
 *
 * Wherever a method takes an address, it also takes the address ID. XRP and XLM addresses can carry their destination
 * tag or memo ID, as in 'r…?dt=123' or 'G…?memoId=123'.
 */
final class Addresses
{
    /**
     * @internal Use Wallet::addresses().
     */
    public function __construct(private readonly Http $http, private readonly string $coin, private readonly string $walletId)
    {
    }

    /**
     * Create a receive address.
     *
     * @param int|null $chain               The address type, as a chain number. Leave null to let BitGo use the wallet's default.
     * @param string|null $label
     * @param bool|null $lowPriority        Account-based coins: deploy the address later, when it's cheaper
     * @param int|null $forwarderVersion    EVM coins: the forwarder contract version
     * @param string|null $onToken          Coins with tokens: create the address for this token
     * @param string|null $format           BCH and BSV: 'base58' or 'cashaddr'
     * @param array<string, mixed> $extra   More body params, for ones this method doesn't name yet
     * @return array{id: string, address: string, coin: string, wallet: string, chain: int, index: int, label?: string, ...}
     * @throws BitGoException
     */
    public function create(
        ?int $chain = null,
        ?string $label = null,
        ?bool $lowPriority = null,
        ?int $forwarderVersion = null,
        ?string $onToken = null,
        ?string $format = null,
        array $extra = [],
    ): array {
        /** @var array{id: string, address: string, coin: string, wallet: string, chain: int, index: int, label?: string, ...} $address BitGo's documented shape, not checked at runtime */
        $address = $this->http->post('{coin}/wallet/{walletId}/address', $this->path(), [
            'chain' => $chain,
            'label' => $label,
            'lowPriority' => $lowPriority,
            'forwarderVersion' => $forwarderVersion,
            'onToken' => $onToken,
            'format' => $format,
        ], $extra);
        return $address;
    }

    /**
     * Get a receive address.
     *
     * @param string $address               The address or its ID
     * @param array<string, mixed> $extra   Query params, for ones this method doesn't name yet
     * @return array{id: string, address: string, coin: string, wallet: string, chain: int, index: int, label?: string, ...}
     * @throws BitGoException
     */
    public function get(string $address, array $extra = []): array
    {
        /** @var array{id: string, address: string, coin: string, wallet: string, chain: int, index: int, label?: string, ...} $found BitGo's documented shape, not checked at runtime */
        $found = $this->http->get('{coin}/wallet/{walletId}/address/{address}', [...$this->path(), 'address' => $address], [], $extra);
        return $found;
    }

    /**
     * Update a receive address.
     *
     * @param string $address               The address or its ID
     * @param string|null $label
     * @param array<string, mixed> $extra   More body params, for ones this method doesn't name yet
     * @return array{id: string, address: string, coin: string, wallet: string, chain: int, index: int, label?: string, ...}
     * @throws BitGoException
     */
    public function update(string $address, ?string $label = null, array $extra = []): array
    {
        /** @var array{id: string, address: string, coin: string, wallet: string, chain: int, index: int, label?: string, ...} $updated BitGo's documented shape, not checked at runtime */
        $updated = $this->http->put('{coin}/wallet/{walletId}/address/{address}', [...$this->path(), 'address' => $address], ['label' => $label], $extra);
        return $updated;
    }

    /**
     * EVM coins: deploy the forwarder contract of a receive address now, instead of when it first receives funds.
     *
     * @param string $address                                                   The address or its ID
     * @param bool|null $forceDeploy                                            Deploy even if the address isn't pending deployment
     * @param int|null $gasPrice                                                In wei. Leave null to use BitGo's network estimate.
     * @param array{maxFeePerGas: int, maxPriorityFeePerGas: int}|null $eip1559 EIP-1559 fees, in wei, instead of $gasPrice
     * @param array<string, mixed> $extra                                       More body params, for ones this method doesn't name yet
     * @return array{txId: string, ...}
     * @throws BitGoException
     */
    public function deploy(string $address, ?bool $forceDeploy = null, ?int $gasPrice = null, ?array $eip1559 = null, array $extra = []): array
    {
        /** @var array{txId: string, ...} $deployed BitGo's documented shape, not checked at runtime */
        $deployed = $this->http->post('{coin}/wallet/{walletId}/address/{address}/deployment', [...$this->path(), 'address' => $address], [
            'forceDeploy' => $forceDeploy,
            'gasPrice' => $gasPrice,
            'eip1559' => $eip1559,
        ], $extra);
        return $deployed;
    }

    /**
     * EVM coins: move the tokens that a receive address holds into the wallet.
     *
     * @param string $address                                                   The address or its ID
     * @param string|null $tokenName                                            The token to forward, for example 'hteth:usdc'
     * @param bool|null $forceFlush                                             Forward even if BitGo doesn't see a balance on the address
     * @param int|null $gasPrice                                                In wei. Leave null to use BitGo's network estimate.
     * @param array{maxFeePerGas: int, maxPriorityFeePerGas: int}|null $eip1559 EIP-1559 fees, in wei, instead of $gasPrice
     * @param array<string, mixed> $extra                                       More body params, for ones this method doesn't name yet
     * @return array{txId: string, ...}
     * @throws BitGoException
     */
    public function forwardTokens(
        string $address,
        ?string $tokenName = null,
        ?bool $forceFlush = null,
        ?int $gasPrice = null,
        ?array $eip1559 = null,
        array $extra = [],
    ): array {
        /** @var array{txId: string, ...} $forwarded BitGo's documented shape, not checked at runtime */
        $forwarded = $this->http->post('{coin}/wallet/{walletId}/address/{address}/tokenforward', [...$this->path(), 'address' => $address], [
            'tokenName' => $tokenName,
            'forceFlush' => $forceFlush,
            'gasPrice' => $gasPrice,
            'eip1559' => $eip1559,
        ], $extra);
        return $forwarded;
    }

    /**
     * Get a proof that the wallet owns a receive address, for Travel Rule checks.
     *
     * BTC trust wallets only, and BitGo has to set up your enterprise's VASP ID first.
     *
     * @param string $address               The address or its ID
     * @param array<string, mixed> $extra   Query params, for ones this method doesn't name yet
     * @return array{iou: bool, address?: string, signature?: string, prefix?: string, proofType?: string, auxProofData?: list<array{type: string, data: array<string, mixed>}>, ...}
     * @throws BitGoException
     */
    public function getOwnershipProof(string $address, array $extra = []): array
    {
        /** @var array{iou: bool, address?: string, signature?: string, prefix?: string, proofType?: string, auxProofData?: list<array{type: string, data: array<string, mixed>}>, ...} $proof BitGo's documented shape, not checked at runtime */
        $proof = $this->http->get('{coin}/wallet/{walletId}/address/{address}/proof', [...$this->path(), 'address' => $address], [], $extra);
        return $proof;
    }

    /**
     * List the wallet's receive addresses. Sends nothing until you iterate, then one request per batch.
     *
     * Each item has the same shape as get() returns.
     *
     * @param string|null $labelContains            Only addresses whose label contains this text
     * @param int|null $limit                       How many addresses each request fetches. This is a batch size, not a total.
     * @param bool|null $mine                       Only addresses created by the current user
     * @param int|null $sort                        1 for oldest first, -1 for newest first
     * @param list<int>|null $chains                Only addresses on these chains
     * @param bool|null $includeBalances            Include each address's balance
     * @param string|null $returnBalancesForToken   Include each address's balance of this token
     * @param bool|null $pendingDeployment          EVM coins: only addresses that are, or aren't, waiting to be deployed
     * @param array<string, mixed> $extra           More query params, for ones this method doesn't name yet
     */
    public function list(
        ?string $labelContains = null,
        ?int $limit = null,
        ?bool $mine = null,
        ?int $sort = null,
        ?array $chains = null,
        ?bool $includeBalances = null,
        ?string $returnBalancesForToken = null,
        ?bool $pendingDeployment = null,
        array $extra = [],
    ): Paginator {
        return new Paginator($this->http, '{coin}/wallet/{walletId}/addresses', $this->path(), [
            'labelContains' => $labelContains,
            'limit' => $limit,
            'mine' => $mine,
            'sort' => $sort,
            'chains' => $chains,
            'includeBalances' => $includeBalances,
            'returnBalancesForToken' => $returnBalancesForToken,
            'pendingDeployment' => $pendingDeployment,
        ], $extra, 'addresses');
    }

    /**
     * @return array{coin: string, walletId: string}
     */
    private function path(): array
    {
        return ['coin' => $this->coin, 'walletId' => $this->walletId];
    }
}
