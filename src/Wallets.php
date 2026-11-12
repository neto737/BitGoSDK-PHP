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

namespace neto737\BitGoSDK;

use neto737\BitGoSDK\Exception\BitGoException;
use neto737\BitGoSDK\Internal\Http;

/**
 * Your wallets of every coin: /api/v2/wallets and /api/v2/wallet/balances.
 *
 * For one coin's wallets, use Coin::wallets(). To work with one wallet, take its coin and ID from here and use
 * BitGo::coin($coin)->wallet($id).
 */
final readonly class Wallets
{
    /**
     * @internal Use BitGo::wallets().
     */
    public function __construct(private Http $http)
    {
    }

    /**
     * List your wallets of every coin. Sends nothing until you iterate, then one request per batch.
     *
     * Each item has the same shape as Wallet::get() returns, and its 'coin' says which coin it is.
     *
     * @param int|null $limit                       How many wallets each request fetches. This is a batch size, not a total.
     * @param list<CoinCode|string>|null $coin      Only wallets of these coins
     * @param list<string>|null $enterprise         Only wallets in these enterprises, by ID
     * @param list<string>|null $type               Only wallets of these types, for example 'hot', 'cold' or 'custodial'
     * @param string|null $labelContains            Only wallets whose label contains this text
     * @param bool|null $deleted                    true for only deleted wallets, false for only wallets that aren't
     * @param bool|null $expandBalance              Include each wallet's balances
     * @param bool|null $excludeSpendableBalance    With $expandBalance, return only confirmed balances, which is faster
     * @param array<string, mixed> $extra           More query params, for ones this method doesn't name yet
     */
    public function list(
        ?int $limit = null,
        ?array $coin = null,
        ?array $enterprise = null,
        ?array $type = null,
        ?string $labelContains = null,
        ?bool $deleted = null,
        ?bool $expandBalance = null,
        ?bool $excludeSpendableBalance = null,
        array $extra = [],
    ): Paginator {
        return new Paginator($this->http, 'wallets', [], [
            'limit' => $limit,
            'coin' => self::codes($coin),
            'enterprise' => $enterprise,
            'type' => $type,
            'labelContains' => $labelContains,
            'deleted' => $deleted,
            'expandBalance' => $expandBalance,
            'excludeSpendableBalance' => $excludeSpendableBalance,
        ], $extra, 'wallets');
    }

    /**
     * The total balances of your wallets, added up per coin. Amounts are strings in base units.
     *
     * @param list<CoinCode|string>|null $coin      Only these coins
     * @param list<string>|null $enterprise         Only wallets in these enterprises, by ID
     * @param list<string>|null $type               Only wallets of these types, for example 'hot', 'cold' or 'custodial'
     * @param string|null $labelContains            Only wallets whose label contains this text
     * @param bool|null $allTokens                  Include the totals of tokens, such as ERC20 and Stellar tokens
     * @param bool|null $excludeEmptyBalances       Leave out tokens with a zero balance
     * @param bool|null $excludeSpendableBalances   Return only confirmed balances, which is faster
     * @param bool|null $includeStakingBalances     Also return the staked and reward totals
     * @param array<string, mixed> $extra           More query params, for ones this method doesn't name yet
     * @return array{balances: list<array{coin: string, balanceString: string, confirmedBalanceString: string, spendableBalanceString?: string, stakingBalanceString?: string, rewardBalanceString?: string, ...}>, ...}
     * @throws BitGoException
     */
    public function balances(
        ?array $coin = null,
        ?array $enterprise = null,
        ?array $type = null,
        ?string $labelContains = null,
        ?bool $allTokens = null,
        ?bool $excludeEmptyBalances = null,
        ?bool $excludeSpendableBalances = null,
        ?bool $includeStakingBalances = null,
        array $extra = [],
    ): array {
        /** @var array{balances: list<array{coin: string, balanceString: string, confirmedBalanceString: string, spendableBalanceString?: string, stakingBalanceString?: string, rewardBalanceString?: string, ...}>, ...} $balances BitGo's documented shape, not checked at runtime */
        $balances = $this->http->get('wallet/balances', [], [
            'coin' => self::codes($coin),
            'enterprise' => $enterprise,
            'type' => $type,
            'labelContains' => $labelContains,
            'allTokens' => $allTokens,
            'excludeEmptyBalances' => $excludeEmptyBalances,
            'excludeSpendableBalances' => $excludeSpendableBalances,
            'includeStakingBalances' => $includeStakingBalances,
        ], $extra);
        return $balances;
    }

    /**
     * @param list<CoinCode|string>|null $coins
     * @return list<string>|null
     */
    private static function codes(?array $coins): ?array
    {
        return $coins === null ? null : array_map(static fn (CoinCode|string $coin): string => $coin instanceof CoinCode ? $coin->value : $coin, $coins);
    }
}
