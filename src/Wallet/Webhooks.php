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
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use neto737\BitGoSDK\Internal\Http;
use neto737\BitGoSDK\Paginator;

/**
 * A wallet's webhooks: /api/v2/{coin}/wallet/{walletId}/webhooks.
 *
 * BitGo POSTs to the webhook's URL when the event happens, for example a 'transfer' or a 'pendingapproval'. Anyone
 * can POST to that URL, so treat the notification as a hint and fetch the transfer from BitGo before acting on it.
 */
final readonly class Webhooks
{
    /**
     * @internal Use Wallet::webhooks().
     */
    public function __construct(private Http $http, private string $coin, private string $walletId)
    {
    }

    /**
     * Add a webhook. BitGo allows up to 10 webhooks of each type per wallet.
     *
     * @param string $type                                  The event, for example 'transfer', 'pendingapproval', 'address_confirmation' or 'txRequest'
     * @param string $url                                   Where BitGo sends the notification
     * @param int|null $numConfirmations                    Notify after this many confirmations. With 0 or null, BitGo notifies when the transfer is first seen and when it confirms.
     * @param bool|null $allToken                           ETH and XLM: also notify on token transfers. Trade accounts need true.
     * @param bool|null $listenToFailureStates              Also notify when the transaction fails on-chain
     * @param list<string>|null $txRequestStates            'txRequest' webhooks: only notify on these states, for example 'pendingApproval' or 'delivered'
     * @param list<string>|null $txRequestTransactionStates 'txRequestTransaction' webhooks: only notify on these states, for example 'signed' or 'delivered'
     * @param array<string, string>|null $customHttpHeaders Headers BitGo adds to every notification
     * @param array<string, mixed> $extra                   More body params, for ones this method doesn't name yet
     * @return array{id: string, coin: string, url: string, type?: string, label?: string, numConfirmations?: int, state?: string, ...}
     * @throws BitGoException
     */
    public function add(
        string $type,
        string $url,
        ?string $label = null,
        ?int $numConfirmations = null,
        ?bool $allToken = null,
        ?bool $listenToFailureStates = null,
        ?array $txRequestStates = null,
        ?array $txRequestTransactionStates = null,
        ?array $customHttpHeaders = null,
        array $extra = [],
    ): array {
        /** @var array{id: string, coin: string, url: string, type?: string, label?: string, numConfirmations?: int, state?: string, ...} $webhook BitGo's documented shape, not checked at runtime */
        $webhook = $this->http->post('{coin}/wallet/{walletId}/webhooks', $this->path(), [
            'type' => $type,
            'url' => $url,
            'label' => $label,
            'numConfirmations' => $numConfirmations,
            'allToken' => $allToken,
            'listenToFailureStates' => $listenToFailureStates,
            'txRequestStates' => $txRequestStates,
            'txRequestTransactionStates' => $txRequestTransactionStates,
            // An object, so that an empty map is sent as {} rather than [].
            'customHttpHeaders' => $customHttpHeaders === null ? null : (object) $customHttpHeaders,
        ], $extra);
        return $webhook;
    }

    /**
     * List the wallet's webhooks. Sends nothing until you iterate.
     *
     * Each item has the same shape as add() returns.
     *
     * @param array<string, mixed> $extra   Query params, for ones this method doesn't name yet
     */
    public function list(array $extra = []): Paginator
    {
        return new Paginator($this->http, '{coin}/wallet/{walletId}/webhooks', $this->path(), [], $extra, 'webhooks');
    }

    /**
     * Remove the webhooks that match every param you pass, for example one webhook by its ID, or by its type and URL.
     *
     * @param string|null $id                   The webhook ID, as add() and list() return it
     * @param array<string, mixed> $extra       More body params, for ones this method doesn't name yet
     * @return array{removed: int, ...}         How many webhooks were removed
     * @throws InvalidArgumentException if no param is passed, before anything is sent
     * @throws BitGoException
     */
    public function remove(?string $type = null, ?string $url = null, ?string $id = null, array $extra = []): array
    {
        if ($type === null && $url === null && $id === null && $extra === []) {
            throw new InvalidArgumentException('Pass the webhook\'s ID, or its type and URL, to say which webhooks to remove.');
        }

        /** @var array{removed: int, ...} $removed BitGo's documented shape, not checked at runtime */
        $removed = $this->http->delete('{coin}/wallet/{walletId}/webhooks', $this->path(), [
            'type' => $type,
            'url' => $url,
            'id' => $id,
        ], $extra);
        return $removed;
    }

    /**
     * Send a test notification to the webhook's URL. Pass the ID that matches the webhook's type, or
     * usePlaceholderData: true to let BitGo make up the data.
     *
     * @param string|null $transferId           'transfer' webhooks: a transfer on this wallet
     * @param string|null $pendingApprovalId    'pendingapproval' webhooks: a pending approval on this wallet
     * @param string|null $txRequestId          'txRequest' and 'txRequestTransaction' webhooks: a transaction request on this wallet
     * @param string|null $txHash               'transaction' and 'transaction_finality_on_l1' webhooks: a transaction hash
     * @param bool|null $usePlaceholderData     Send generated placeholder data instead of a real transfer or approval
     * @param array<string, mixed> $extra       More body params, for ones this method doesn't name yet
     * @return array{webhookNotifications: list<array<string, mixed>>, ...}
     * @throws BitGoException
     */
    public function simulate(
        string $webhookId,
        ?string $transferId = null,
        ?string $pendingApprovalId = null,
        ?string $txRequestId = null,
        ?string $txHash = null,
        ?bool $usePlaceholderData = null,
        array $extra = [],
    ): array {
        /** @var array{webhookNotifications: list<array<string, mixed>>, ...} $simulated BitGo's documented shape, not checked at runtime */
        $simulated = $this->http->post('{coin}/wallet/{walletId}/webhooks/{webhookId}/simulate', [...$this->path(), 'webhookId' => $webhookId], [
            'transferId' => $transferId,
            'pendingApprovalId' => $pendingApprovalId,
            'txRequestId' => $txRequestId,
            'txHash' => $txHash,
            'usePlaceholderData' => $usePlaceholderData,
        ], $extra);
        return $simulated;
    }

    /**
     * @return array{coin: string, walletId: string}
     */
    private function path(): array
    {
        return ['coin' => $this->coin, 'walletId' => $this->walletId];
    }
}
