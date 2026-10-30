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
 * The current user's webhooks on a coin, not tied to any wallet: /api/v2/{coin}/webhooks.
 *
 * Their type is 'block', which fires when BitGo sees a new block on the coin's network, or 'wallet_confirmation',
 * which fires when a wallet of the coin is initialized.
 */
final class BlockWebhooks
{
    /**
     * @internal Use Coin::blockWebhooks().
     */
    public function __construct(private readonly Http $http, private readonly string $coin)
    {
    }

    /**
     * Add a webhook.
     *
     * @param string $type                  'block' or 'wallet_confirmation'
     * @param string $url                   Where BitGo sends the notification
     * @param string|null $label
     * @param int|null $numConfirmations    Notify after this many confirmations
     * @param array<string, mixed> $extra   More body params, for ones this method doesn't name yet
     * @return array{id: string, coin: string, url: string, type?: string, label?: string, numConfirmations?: int, state?: string, ...}
     * @throws BitGoException
     */
    public function add(string $type, string $url, ?string $label = null, ?int $numConfirmations = null, array $extra = []): array
    {
        /** @var array{id: string, coin: string, url: string, type?: string, label?: string, numConfirmations?: int, state?: string, ...} $webhook BitGo's documented shape, not checked at runtime */
        $webhook = $this->http->post('{coin}/webhooks', $this->path(), [
            'type' => $type,
            'url' => $url,
            'label' => $label,
            'numConfirmations' => $numConfirmations,
        ], $extra);
        return $webhook;
    }

    /**
     * List the webhooks. Sends nothing until you iterate.
     *
     * Each item has the same shape as add() returns.
     *
     * @param array<string, mixed> $extra   Query params, for ones this method doesn't name yet
     */
    public function list(array $extra = []): Paginator
    {
        return new Paginator($this->http, '{coin}/webhooks', $this->path(), [], $extra, 'webhooks');
    }

    /**
     * Remove the webhooks with this type and URL, or only the one with this ID among them.
     *
     * @param string $type                  'block' or 'wallet_confirmation'
     * @param string $url
     * @param string|null $id               The webhook ID, as add() and list() return it
     * @param array<string, mixed> $extra   More body params, for ones this method doesn't name yet
     * @return array{removed: int, ...}     How many webhooks were removed
     * @throws BitGoException
     */
    public function remove(string $type, string $url, ?string $id = null, array $extra = []): array
    {
        /** @var array{removed: int, ...} $removed BitGo's documented shape, not checked at runtime */
        $removed = $this->http->delete('{coin}/webhooks', $this->path(), [
            'type' => $type,
            'url' => $url,
            'id' => $id,
        ], $extra);
        return $removed;
    }

    /**
     * Send a test notification to the webhook's URL.
     *
     * @param string $webhookId
     * @param string|null $blockId          The block to notify about. Leave null to let BitGo choose.
     * @param array<string, mixed> $extra   More body params, for ones this method doesn't name yet
     * @return array{webhookNotifications: list<array<string, mixed>>, ...}
     * @throws BitGoException
     */
    public function simulate(string $webhookId, ?string $blockId = null, array $extra = []): array
    {
        /** @var array{webhookNotifications: list<array<string, mixed>>, ...} $simulated BitGo's documented shape, not checked at runtime */
        $simulated = $this->http->post('{coin}/webhooks/{webhookId}/simulate', [...$this->path(), 'webhookId' => $webhookId], ['blockId' => $blockId], $extra);
        return $simulated;
    }

    /**
     * @return array{coin: string}
     */
    private function path(): array
    {
        return ['coin' => $this->coin];
    }
}
