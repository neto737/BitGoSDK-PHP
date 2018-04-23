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

interface BitGoExpressInterface {

    // USER AUTHENTICATION
    public function login(string $email, string $password, string $otp, bool $extensible = false);

    // WALLETS
    public function generateWallet(string $label, string $passphrase, string $userKey = null, string $backupXpub = null, string $backupXpubProvider = null, string $enterprise = null, bool $disableTransactionNotifications = null, int $gasPrice = null, string $passcodeEncryptionCode = null);

    public function addWallet(string $label, int $m, int $n, array $keys, string $enterprise = null, bool $isCold = null, bool $disableTransactionNotifications = null);

    // WALLET OPERATIONS
    public function sendTransaction(string $address, int $amount, string $walletPassphrase, string $prv = null, int $numBlocks = null, int $feeRate = null, string $comment = null, array $unspents = null, int $minConfirms = null, bool $enforceMinConfirmsForChange = null, int $targetWalletUnspents = null, bool $noSplitChange = null, int $minValue = null, int $maxValue = null, int $gasPrice = null, int $gasLimit = null, int $sequenceId = null, bool $segwit = null, int $lastLedgerSequence = null, string $ledgerSequenceDelta = null);

    public function sendTransactionToMany(array $recipients, string $walletPassphrase, string $prv = null, int $numBlocks = null, int $feeRate = null, string $comment = null, array $unspents = null, int $minConfirms = null, bool $enforceMinConfirmsForChange = null, int $targetWalletUnspents = null, bool $noSplitChange = null, int $minValue = null, int $maxValue = null, int $gasPrice = null, int $gasLimit = null, int $sequenceId = null, bool $segwit = null, int $lastLedgerSequence = null, string $ledgerSequenceDelta = null);

    // WALLET OPERATIONS - ADVANCED
    public function consolidateWalletUnspents(string $walletPassphrase, int $numUnspentsToMake = 1, int $limit = 25, int $minValue = null, int $maxValue = null, int $minHeight = null, int $feeRate = null, int $feeTxConfirmTarget = null, int $maxFeePercentage = null, int $minConfirms = null, bool $enforceMinConfirmsForChange = null);

    public function funoutWalletUnspents(string $walletPassphrase, string $xprv = null, int $maxNumInputsToUse = null, int $numUnspentsToMake = null, int $minValue = null, int $maxValue = null, int $minHeight = null, int $maxFeePercentage = null, int $minConfirms = null, bool $enforceMinConfirmsForChange = null, int $feeRate = null, int $feeTxConfirmTarget = null);

    public function sweep(string $address, string $walletPassphrase, string $xprv = null, string $otp = null, int $feeRate = null, int $feeTxConfirmTarget = null, int $gasPrice = null, int $sequenceId = null, int $lastLedgerSequence = null, string $ledgerSequenceDelta = null);

    public function buildTransaction(array $recipients, int $numBlocks = null, int $feeRate = null, int $minConfirms = null, bool $enforceMinConfirmsForChange = null, array $unspents = null, int $targetWalletUnspents = null, bool $noSplitChange = null, int $minValue = null, int $maxValue = null, int $gasPrice = null, int $lastLedgerSequence = null, string $ledgerSequenceDelta = null);

    public function signBuildTransaction($txPrebuild, string $prv = null, string $coldDerivationSeed = null, $keychain = null, string $walletPassphrase = null);

    public function sendBuildTransaction($halfSigned, string $txHex, string $otp, string $comment = null);

    // KEYCHAIN
    public function createKeychain();

    // WALLET SHARING
    public function shareWallet(string $email, string $permissions, string $walletPassphrase = null, bool $skipKeychain = null, bool $disableEmail = null);

    public function acceptWalletShare(string $walletShareId, string $newWalletPassphrase = null, string $userPassword = null, string $overrideEncryptedPrv = null);

    // PENDING APPROVALS
    public function updatePendingApproval(string $pendingApprovalId, string $state, string $otp);
}
