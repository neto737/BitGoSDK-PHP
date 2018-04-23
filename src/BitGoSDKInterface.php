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
 * @version 2.1
 */

namespace neto737\BitGoSDK;

interface BitGoSDKInterface {

    // USER AUTHENTICATION
    public function getCurrentUserProfile();
    
    public function getSessionInfo();
    
    public function lockSession();
    
    public function unlockSession(string $otp, int $duration = 3600);
    
    public function logout();
    
    // WALLETS
    public function listWallets(int $limit = 25, string $prevId = null, bool $allTokens = false);

    public function getWallet(bool $allTokens = false);

    public function getWalletByAddress(string $walletAddress);

    // WALLET OPERATIONS
    public function listWalletTransfers(string $prevId = null, bool $allTokens = false);

    public function getWalletTransfer(string $transactionId);

    public function getWalletTransferBySequenceId(string $sequenceId);

    public function createWalletAddress(bool $allowMigrated = false, int $chain = 0, int $gasPrice = null, string $label = null);

    public function getWalletAddress(string $addressOrId);

    public function updateWalletAddress(string $addressOrId, string $label);

    // WALLET OPERATIONS - ADVANCED
    public function getWalletAddresses(int $limit = null, string $prevId = null, int $sortOrder = null);

    public function getWalletTransactions(string $prevId = null, bool $allTokens = false);

    public function getWalletTransaction(string $transactionId);

    public function listWalletUnspents(string $prevId = null, int $minValue = null, int $maxValue = null, int $minHeight = null, int $minConfirms = null);

    public function getTotalBalances();

    public function getMaximumSpendable(int $limit = null, int $minValue = null, int $maxValue = null, int $minHeight = null, int $feeRate = null, int $minConfirms = null, bool $enforceMinConfirmsForChange = null);

    public function freezeWallet(string $otp, int $duration = 3600);

    // KEYCHAINS
    public function listKeychains(int $limit = 25, string $prevId = null);

    public function getKeychain(string $keyId);

    public function createBitGoKeychain(string $source = 'bitgo', string $enterprise = null, bool $newFeeAddress = null);

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

    public function removeWalletUser(string $userId);

    // PENDING APPROVALS
    public function getPendingApproval(string $pendingApprovalId);

    public function listPendingApprovals(string $walletID = null, string $enterprise = null, bool $allTokens = false);

    // WEBHOOKS
    public function listWalletWebhooks(bool $allTokens = false);

    public function addWalletWebhook(string $url, string $type, int $numConfirmations = null);

    public function removeWalletWebhook(string $url, string $type);

    public function simulateWalletWebhook(string $webhookId);

    public function listUserWebhooks();

    public function addUserWebhook(string $url, string $type, string $label = null, int $numConfirmations = null);

    public function removeUserWebhook(string $url, string $type);

    public function simulateUserWebhook(string $webhookId, string $blockId = null);

    // UTILITIES
    public function getMarketPriceData();

    public function estimateTransactionFees(int $numBlocks = null);
}
