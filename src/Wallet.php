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
use neto737\BitGoSDK\Wallet\Addresses;
use neto737\BitGoSDK\Wallet\Webhooks;

/**
 * One wallet. It holds only the coin and the wallet ID, never the wallet's data, so nothing in it can go stale.
 */
final readonly class Wallet
{
    /**
     * @internal Use Coin::wallet().
     */
    public function __construct(private Http $http, public string $coin, public string $id)
    {
    }

    /**
     * Get the wallet. Sends a request every time, so balances are always current.
     *
     * @param bool|null $allTokens      Account-based coins: include the balances of all tokens
     * @param array<string, mixed> $extra   More query params, for ones this method doesn't name yet
     * @return array{id: string, coin: string, label: string, balanceString?: string, confirmedBalanceString?: string, spendableBalanceString?: string, ...}
     * @throws BitGoException
     */
    public function get(?bool $allTokens = null, array $extra = []): array
    {
        /** @var array{id: string, coin: string, label: string, balanceString?: string, confirmedBalanceString?: string, spendableBalanceString?: string, ...} $wallet BitGo's documented shape, not checked at runtime */
        $wallet = $this->http->get('{coin}/wallet/{walletId}', $this->path(), ['allTokens' => $allTokens], $extra);
        return $wallet;
    }

    /**
     * Update the wallet's settings. Only the params you pass are changed.
     *
     * @param string|null $label                            A new name for the wallet
     * @param int|null $approvalsRequired                   How many admins must approve a pending approval, 1 or more
     * @param bool|null $disableTransactionNotifications    Stop BitGo's emails about the wallet's transactions
     * @param array<string, mixed>|null $buildDefaults      UTXO coins: defaults for building transactions, such as 'feeMultiplier', 'changeAddressType' or 'txFormat'
     * @param array<string, mixed>|null $coinSpecific       Settings for one coin, keyed by its code, for example ['teth' => ['deployForwardersManually' => true]]
     * @param array<string, mixed> $extra                   More body params, for ones this method doesn't name yet
     * @return array{id: string, coin: string, label: string, balanceString?: string, confirmedBalanceString?: string, spendableBalanceString?: string, ...}
     * @throws BitGoException
     */
    public function update(
        ?string $label = null,
        ?int $approvalsRequired = null,
        ?bool $disableTransactionNotifications = null,
        ?array $buildDefaults = null,
        ?array $coinSpecific = null,
        array $extra = [],
    ): array {
        /** @var array{id: string, coin: string, label: string, balanceString?: string, confirmedBalanceString?: string, spendableBalanceString?: string, ...} $wallet BitGo's documented shape, not checked at runtime */
        $wallet = $this->http->put('{coin}/wallet/{walletId}', $this->path(), [
            'label' => $label,
            'approvalsRequired' => $approvalsRequired,
            'disableTransactionNotifications' => $disableTransactionNotifications,
            'buildDefaults' => $buildDefaults,
            'coinSpecific' => $coinSpecific,
        ], $extra);
        return $wallet;
    }

    /**
     * Freeze the wallet: block every send from it until the freeze expires. A freeze can't be lifted early.
     *
     * @param int $duration                 How long to freeze the wallet, in seconds
     * @param string|null $reason           Why, for example 'UserRequest' or 'TxIntentMismatch'
     * @param string|null $referenceId      Your own ID for the freeze, for tracking
     * @param array<string, mixed> $extra   More body params, for ones this method doesn't name yet
     * @return array{time: string, expires: string, reason?: string, referenceId?: string, adminOrUserId?: string, ...}
     * @throws BitGoException
     */
    public function freeze(int $duration = 3600, ?string $reason = null, ?string $referenceId = null, array $extra = []): array
    {
        /** @var array{time: string, expires: string, reason?: string, referenceId?: string, adminOrUserId?: string, ...} $freeze BitGo's documented shape, not checked at runtime */
        $freeze = $this->http->post('{coin}/wallet/{walletId}/freeze', $this->path(), [
            'duration' => $duration,
            'reason' => $reason,
            'referenceId' => $referenceId,
        ], $extra);
        return $freeze;
    }

    /**
     * The most the wallet can send in one transaction, after fees.
     *
     * The filters are for UTXO coins: they pick which unspents count. Values are in base units.
     *
     * @param int|null $limit                           Use at most this many unspents, up to 500
     * @param Amount|string|int|null $minValue          Ignore unspents smaller than this
     * @param Amount|string|int|null $maxValue          Ignore unspents larger than this
     * @param int|null $minHeight                       Ignore unspents below this block height
     * @param int|null $minConfirms                     Ignore unspents from other wallets with fewer confirmations than this
     * @param bool|null $enforceMinConfirmsForChange    Apply $minConfirms to change unspents too
     * @param int|null $feeRate                         The fee rate to use, in base units per kB
     * @param int|null $maxFeeRate                      The highest fee rate to use when BitGo estimates it
     * @param int|null $numBlocks                       BTC: estimate a fee rate that confirms within this many blocks
     * @param list<string>|null $unspentIds             Use only these unspents
     * @param array<string, mixed> $extra               More query params, for ones this method doesn't name yet
     * @return array{coin: string, maximumSpendable: string, ...}
     * @throws BitGoException
     */
    public function maximumSpendable(
        ?int $limit = null,
        Amount|string|int|null $minValue = null,
        Amount|string|int|null $maxValue = null,
        ?int $minHeight = null,
        ?int $minConfirms = null,
        ?bool $enforceMinConfirmsForChange = null,
        ?int $feeRate = null,
        ?int $maxFeeRate = null,
        ?int $numBlocks = null,
        ?array $unspentIds = null,
        array $extra = [],
    ): array {
        /** @var array{coin: string, maximumSpendable: string, ...} $spendable BitGo's documented shape, not checked at runtime */
        $spendable = $this->http->get('{coin}/wallet/{walletId}/maximumSpendable', $this->path(), [
            'limit' => $limit,
            'minValue' => $minValue,
            'maxValue' => $maxValue,
            'minHeight' => $minHeight,
            'minConfirms' => $minConfirms,
            'enforceMinConfirmsForChange' => $enforceMinConfirmsForChange,
            'feeRate' => $feeRate,
            'maxFeeRate' => $maxFeeRate,
            'numBlocks' => $numBlocks,
            'unspentIds' => $unspentIds,
        ], $extra);
        return $spendable;
    }

    /**
     * The wallet's receive addresses. Sends no request.
     */
    public function addresses(): Addresses
    {
        return new Addresses($this->http, $this->coin, $this->id);
    }

    /**
     * The wallet's webhooks. Sends no request.
     */
    public function webhooks(): Webhooks
    {
        return new Webhooks($this->http, $this->coin, $this->id);
    }

    /**
     * @return array{coin: string, walletId: string}
     */
    private function path(): array
    {
        return ['coin' => $this->coin, 'walletId' => $this->id];
    }
}
