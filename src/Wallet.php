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
