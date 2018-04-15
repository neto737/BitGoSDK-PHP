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
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3
 * @version 2.0
 */

namespace neto737\BitGoSDK;

interface BitGoSDKInterface {

    // WALLETS
    public function listWallets();

    public function getWallet(string $walletId);

    public function getWalletByAddress(string $walletAddress);

    // WALLET OPERATIONS
    public function listWalletTransfers(string $walletId);

    public function getWalletTransfer(string $walletId, string $transactionId);

    public function getWalletTransferBySequenceId(string $walletId, string $sequenceId);

    public function createWalletAddress(string $walletId);

    public function getWalletAddress(string $walletId, string $addressOrId);

    public function updateWalletAddress(string $walletId, string $addressOrId, string $label);

    // WALLET OPERATIONS - ADVANCED
    public function getWalletAddresses(string $walletId);

    public function getWalletTransactions(string $walletId);

    public function getWalletTransaction(string $walletId, string $transactionId);

    public function listWalletUnspents(string $walletId);

    public function getTotalBalances();

    public function getMaximumSpendable(string $walletId);

    public function freezeWallet(string $walletId, int $duration, string $otp);

    // KEYCHAINS
    public function listKeychains();

    public function getKeychain(string $keyId);

    public function createBitGoKeychain(string $enterprise, string $source = 'bitgo');

    public function createBackupKeychain(string $provider, string $source = 'bitgo');

    public function addKeychain(string $pub, string $encryptedPrv = '', string $source = 'bitgo');

    // WALLET POLICY
    public function setVelocityPolicyRule(string $id, string $type, string $condition, string $action);

    public function updateVelocityPolicyRule(string $id, string $type, string $condition, string $action);

    public function removePolicyRule(string $id, string $type, string $condition, string $action);

    // WALLET SHARING
    public function listWalletShares();

    public function getWalletShare(string $shareId);

    public function resendWalletShareInvite(string $shareId);

    public function cancelWalletShare(string $shareId);

    public function removeWalletUser(string $walletId, string $userId);

    // PENDING APPROVALS
    public function getPendingApproval(string $pendingApprovalId);

    public function listPendingApprovals();

    // WEBHOOKS
    public function listWalletWebhooks(string $walletId);

    public function addWalletWebhook(string $walletId, string $url, string $type);

    public function removeWalletWebhook(string $walletId, string $url, string $type);

    public function simulateWalletWebhook(string $walletId, string $webhookId);

    public function listUserWebhooks();

    public function addUserWebhook(string $url, string $type);

    public function removeUserWebhook(string $url, string $type);

    public function simulateUserWebhook(string $webhookId);

    // UTILITIES
    public function getMarketPriceData();

    public function estimateTransactionFees();
}
