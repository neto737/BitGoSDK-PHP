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

namespace neto737\BitGoSDK\Coin;

use neto737\BitGoSDK\Exception\BitGoException;
use neto737\BitGoSDK\Internal\Http;
use neto737\BitGoSDK\Paginator;

/**
 * Your wallets of one coin: /api/v2/{coin}/wallet.
 *
 * To work with one wallet, take its ID from here and use Coin::wallet($id).
 */
final readonly class Wallets
{
    /**
     * @internal Use Coin::wallets().
     */
    public function __construct(private Http $http, private string $coin)
    {
    }

    /**
     * List your wallets of this coin. Sends nothing until you iterate, then one request per batch.
     *
     * Each item has the same shape as Wallet::get() returns.
     *
     * @param int|null $limit                       How many wallets each request fetches, up to 500. This is a batch size, not a total.
     * @param bool|null $allTokens                  Include the data of the coin's tokens, such as ERC20 and Stellar tokens
     * @param bool|null $excludeSpendableBalance    Return only confirmed balances, which is faster
     * @param string|null $searchLabel              Only wallets whose label contains this text
     * @param bool|null $showAllWallets             Also include deleted wallets and wallets whose creation failed
     * @param array<string, mixed> $extra           More query params, for ones this method doesn't name yet
     */
    public function list(
        ?int $limit = null,
        ?bool $allTokens = null,
        ?bool $excludeSpendableBalance = null,
        ?string $searchLabel = null,
        ?bool $showAllWallets = null,
        array $extra = [],
    ): Paginator {
        return new Paginator($this->http, '{coin}/wallet', $this->path(), [
            'limit' => $limit,
            'allTokens' => $allTokens,
            'excludeSpendableBalance' => $excludeSpendableBalance,
            'searchLabel' => $searchLabel,
            'showAllWallets' => $showAllWallets,
        ], $extra, 'wallets');
    }

    /**
     * Find the wallet that a receive address belongs to.
     *
     * @param string $address               A receive address of this coin. XRP and XLM addresses can carry their tag or memo ID, as in 'r…?dt=123'.
     * @param array<string, mixed> $extra   Query params, for ones this method doesn't name yet
     * @return array{id: string, coin: string, label: string, balanceString?: string, confirmedBalanceString?: string, spendableBalanceString?: string, ...}
     * @throws BitGoException
     */
    public function findByAddress(string $address, array $extra = []): array
    {
        /** @var array{id: string, coin: string, label: string, balanceString?: string, confirmedBalanceString?: string, spendableBalanceString?: string, ...} $wallet BitGo's documented shape, not checked at runtime */
        $wallet = $this->http->get('{coin}/wallet/address/{address}', [...$this->path(), 'address' => $address], [], $extra);
        return $wallet;
    }

    /**
     * The total balances of your wallets of this coin, added up. Amounts are strings in base units.
     *
     * For every coin at once, use BitGo::wallets()->balances().
     *
     * @param bool|null $allTokens                  Include the totals of the coin's tokens, keyed by token code under 'tokens'
     * @param list<string>|null $enterprise         Only wallets in these enterprises, by ID
     * @param bool|null $showAllWallets             Also count deleted wallets and wallets whose creation failed
     * @param bool|null $includeStakingBalances     Also return the staked and reward totals
     * @param array<string, mixed> $extra           More query params, for ones this method doesn't name yet
     * @return array{balanceString: string, confirmedBalanceString: string, spendableBalanceString: string, stakedBalanceString?: string, tokens?: array<string, array{balanceString: string, confirmedBalanceString: string, spendableBalanceString: string, ...}>, ...}
     * @throws BitGoException
     */
    public function balances(
        ?bool $allTokens = null,
        ?array $enterprise = null,
        ?bool $showAllWallets = null,
        ?bool $includeStakingBalances = null,
        array $extra = [],
    ): array {
        /** @var array{balanceString: string, confirmedBalanceString: string, spendableBalanceString: string, stakedBalanceString?: string, tokens?: array<string, array{balanceString: string, confirmedBalanceString: string, spendableBalanceString: string, ...}>, ...} $balances BitGo's documented shape, not checked at runtime */
        $balances = $this->http->get('{coin}/wallet/balances', $this->path(), [
            'allTokens' => $allTokens,
            'enterprise' => $enterprise,
            'showAllWallets' => $showAllWallets,
            'includeStakingBalances' => $includeStakingBalances,
        ], $extra);
        return $balances;
    }

    /**
     * @return array{coin: string}
     */
    private function path(): array
    {
        return ['coin' => $this->coin];
    }
}
