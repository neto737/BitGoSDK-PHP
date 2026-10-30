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

use neto737\BitGoSDK\Coin\BlockWebhooks;
use neto737\BitGoSDK\Coin\Wallets;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use neto737\BitGoSDK\Internal\Http;
use neto737\BitGoSDK\Internal\Path;

/**
 * One coin, for example 'tbtc'. Everything under /api/v2/{coin}/ starts here.
 */
final readonly class Coin
{
    /**
     * @internal Use BitGo::coin().
     */
    public function __construct(private Http $http, public string $code)
    {
    }

    /**
     * A wallet of this coin, by its ID. Sends no request: call get() on it for the wallet's data.
     *
     * @throws InvalidArgumentException if $id is empty, '.' or '..'
     */
    public function wallet(string $id): Wallet
    {
        Path::check($id);
        return new Wallet($this->http, $this->code, $id);
    }

    /**
     * Your wallets of this coin, to list them or find one by address. Sends no request.
     */
    public function wallets(): Wallets
    {
        return new Wallets($this->http, $this->code);
    }

    /**
     * Your webhooks on this coin that aren't tied to a wallet, such as new-block notifications. Sends no request.
     */
    public function blockWebhooks(): BlockWebhooks
    {
        return new BlockWebhooks($this->http, $this->code);
    }
}
