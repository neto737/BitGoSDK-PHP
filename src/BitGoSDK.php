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

use neto737\BitGoSDK\Enum\CurrencyCode;

class BitGoSDK implements IBitGoSDK {

    const BITGO_PRODUCTION_API_ENDPOINT = 'https://www.bitgo.com/api/v2/';
    const BITGO_TESTNET_API_ENDPOINT = 'https://test.bitgo.com/api/v2/';

    private $APIEndpoint = null;
    private $AuthAPIEndpoint = null;
    private $url = null;
    private $params = [];
    private $allowedCoins = ['btc', 'bch', 'bsv', 'btg', 'eth', 'dash', 'ltc', 'xrp', 'zec', 'rmg', 'erc', 'omg', 'zrx', 'fun', 'gnt', 'rep', 'bat', 'knc', 'cvc', 'eos', 'qrl', 'nmr', 'pay', 'brd', 'tbtc', 'tbch', 'tbsv', 'teth', 'tdash', 'tltc', 'txrp', 'tzec', 'trmg', 'terc'];
    public $walletId = null;

    /**
     * BitGoSDK Initialization
     * 
     * @param string $accessToken   Set your Access Token (API Key)
     * @param string $coin          Select the coin what you want to use with the BitGOSDK (use CurrencyCode class to select)
     * @param bool $testNet         Start instance as testnet (true or false), default is false
     * @throws \Exception
     */
    public function __construct(string $accessToken, string $coin = CurrencyCode::BITCOIN, bool $testNet = false) {
        $this->accessToken = $accessToken;
        $this->coin = $coin;
        $this->testNet = $testNet;
        $this->APIEndpoint = (!$this->testNet) ? self::BITGO_PRODUCTION_API_ENDPOINT . $this->coin : self::BITGO_TESTNET_API_ENDPOINT . $this->coin;
        $this->AuthAPIEndpoint = (!$this->testNet) ? self::BITGO_PRODUCTION_API_ENDPOINT : self::BITGO_TESTNET_API_ENDPOINT;

        if (!in_array($this->coin, $this->allowedCoins)) {
            throw new \Exception('You are trying to use an invalid coin');
        }
    }

    /**
     * Converts value in satoshi to BTC value
     * 
     * @param int $amount   amount in satoshi that you want to convert to BTC
     * @return float        value converted to BTC
     */
    public static function toBTC(int $amount) {
        return (float) sprintf('%.8f', bcdiv($amount, 100000000, 8));
    }

    /**
     * Converts value in BTC to satoshi value
     * 
     * @param float $amount amount in BTC that you want to convert to satoshi
     * @return int          value converted to satoshi
     */
    public static function toSatoshi(float $amount) {
        return (int) bcmul(sprintf('%.8f', $amount), 100000000, 0);
    }

    /**
     * This API call retrieves information about the current authenticated user.
     * 
     * @return array
     */
    public function getCurrentUserProfile() {
        $this->url = $this->AuthAPIEndpoint . 'user/me';
        return $this->__execute('GET');
    }

    /**
     * This API call retrieves information about the current session access token.
     * 
     * @return array
     */
    public function getSessionInfo() {
        $this->url = $this->AuthAPIEndpoint . 'user/session';
        return $this->__execute('GET');
    }

    /**
     * Locks the current session, preventing future automated sends without a subsequent unlock (OTP will be required for the unlock).
     * 
     * @return array
     */
    public function lockSession() {
        $this->url = $this->AuthAPIEndpoint . 'user/lock';
        return $this->__execute();
    }

    /**
     * Unlock the current session, which is required for certain other sensitive API calls.
     * 
     * @param string $otp   An OTP code for the account obtained using the second factor authentication device
     * @param int $duration Desired duration of the unlock in seconds (default=600, max=3600)
     * @return array
     */
    public function unlockSession(string $otp, int $duration = 600) {
        $this->url = $this->AuthAPIEndpoint . 'user/unlock';
        $this->params = [
            'otp' => $otp,
            'duration' => $duration
        ];
        return $this->__execute();
    }

    /**
     * Log out of the BitGo service. This will disable an access token.
     * 
     * @return array
     */
    public function logout() {
        $this->url = $this->AuthAPIEndpoint . 'user/logout';
        return $this->__execute('GET');
    }

    /**
     * This API call lists all of a user’s wallets for a given coin.
     * 
     * @param int $limit        Max number of results in a single call. Defaults to 25.
     * @param string $prevId    Continue iterating wallets from this prevId as provided by nextBatchPrevId in the previous list
     * @param bool $allTokens   Gets details of all tokens associated with this wallet. Only valid for ETH/TETH
     * @return array
     */
    public function listWallets(int $limit = 25, string $prevId = null, bool $allTokens = false) {
        $this->url = $this->APIEndpoint . '/wallet';
        $this->params = [
            'limit' => $limit,
            'prevId' => $prevId,
            'allTokens' => $this->coin === CurrencyCode::ETHEREUM || $this->coin === CurrencyCode::ETHEREUM_TESTNET ? $allTokens : null,
        ];
        return $this->__execute('GET');
    }

    /**
     * This API call retrieves wallet object information by the wallet ID.
     * 
     * @param bool $allTokens   Gets details of all tokens associated with this wallet. Only valid for ETH/TETH
     * @return array
     */
    public function getWallet(bool $allTokens = false) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId;
        $this->params = [
            'allTokens' => $this->coin === CurrencyCode::ETHEREUM || $this->coin === CurrencyCode::ETHEREUM_TESTNET ? $allTokens : null,
        ];
        return $this->__execute('GET');
    }

    /**
     * This API call retrieves wallet object information by an address belonging to the wallet.
     * 
     * @param string $walletAddress The address
     * @return array
     */
    public function getWalletByAddress(string $walletAddress) {
        $this->url = $this->APIEndpoint . '/wallet/address/' . $walletAddress;
        return $this->__execute('GET');
    }

    /**
     * Retrieves a list of transfers, which correspond to the deposits and withdrawals of digital currency on a wallet.
     * 
     * @param string $prevId    Continue iterating from this prevId (provided by nextBatchPrevId in the previous list)
     * @param bool $allTokens   Gets transfers of all tokens associated with this wallet. Only valid for ETH/TETH.
     * @return array
     */
    public function listWalletTransfers(string $prevId = null, bool $allTokens = false) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/transfer';
        $this->params = [
            'prevId' => $prevId,
            'allTokens' => $this->coin === CurrencyCode::ETHEREUM || $this->coin === CurrencyCode::ETHEREUM_TESTNET ? $allTokens : null,
        ];
        return $this->__execute('GET');
    }

    /**
     * Wallet transfers represent digital currency sends and receives on your wallet.
     * 
     * @param string $transactionId ID of the wallet transfer
     * @return array
     */
    public function getWalletTransfer(string $transactionId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/transfer/' . $transactionId;
        return $this->__execute('GET');
    }

    /**
     * Get the transaction on a wallet sequence ID that was passed in when sending a transaction 
     * (via Send Transaction or Send Transaction to Many).
     * 
     * @param string $sequenceId    The unique id previously sent with an outgoing transaction
     * @return array
     */
    public function getWalletTransferBySequenceId(string $sequenceId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/transfer/sequenceId/' . $sequenceId;
        return $this->__execute('GET');
    }

    /**
     * This API call is used to create a new receive address for your wallet, which enhances your privacy.
     * 
     * @param bool $allowMigrated   Set to true to enable address creation for migrated BCH wallets.
     * @param int $chain            Specifies the address format, defaults to 0, use 10 for SegWit (only on BTC and BTG)
     * @param int $gasPrice         Custom gas price to be used for deployment of receive addresses (only for Ethereum)
     * @param string $label         Human-readable name for the address
     * @return array
     */
    public function createWalletAddress(bool $allowMigrated = false, int $chain = 0, int $gasPrice = null, string $label = null) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/address';
        $this->params = [
            'id' => $this->walletId,
            'allowMigrated' => $allowMigrated,
            'chain' => $this->coin === CurrencyCode::BITCOIN || $this->coin === CurrencyCode::BITCOIN_GOLD ? $chain : 0,
            'gasPrice' => $this->coin === CurrencyCode::ETHEREUM ? $gasPrice : null,
            'label' => $label
        ];
        return $this->__execute();
    }

    /**
     * This API call is used to get information about a single wallet address.
     * 
     * @param string $addressOrId   Valid address or address id of the wallet.
     * @return array
     */
    public function getWalletAddress(string $addressOrId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/address/' . $addressOrId;
        return $this->__execute('GET');
    }

    /**
     * This API call is used to update fields on a wallet address.
     * 
     * @param string $addressOrId   Valid address or address id of the wallet.
     * @param string $label         Human-readable name for the address
     * @return array
     */
    public function updateWalletAddress(string $addressOrId, string $label) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/address/' . $addressOrId;
        $this->params = [
            'label' => $label
        ];
        return $this->__execute('PUT');
    }

    /**
     * This API call is used to retrieve a wallet’s addresses.
     * 
     * @param int $limit        The maximum number of addresses to be returned.
     * @param string $prevId    Continue iterating (provided by nextBatchPrevId in the previous list)
     * @param int $sortOrder    Order the addresses by creation date. -1 is newest first, 1 is for oldest first.
     * @return array
     */
    public function getWalletAddresses(int $limit = null, string $prevId = null, int $sortOrder = null) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/addresses';
        $this->params = [
            'limit' => $limit,
            'prevId' => $prevId,
            'sortOrder' => $sortOrder
        ];
        return $this->__execute('GET');
    }

    /**
     * This API call lists all blockchain transactions for a given wallet.
     * 
     * @param string $prevId    Continue iterating (provided by nextBatchPrevId in the previous list result)
     * @param bool $allTokens   Gets details of all token transactions associated with this wallet. Only valid for ETH/TETH.
     * @return array
     */
    public function getWalletTransactions(string $prevId = null, bool $allTokens = false) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/tx';
        $this->params = [
            'prevId' => $prevId,
            'allTokens' => $this->coin === CurrencyCode::ETHEREUM || $this->coin === CurrencyCode::ETHEREUM_TESTNET ? $allTokens : null,
        ];
        return $this->__execute('GET');
    }

    /**
     * This API call retrieves object information for a specific transaction hash by specifying the transaction ID.
     * 
     * @param string $transactionId Transaction hash to retrieve.
     * @return array
     */
    public function getWalletTransaction(string $transactionId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/tx/' . $transactionId;
        return $this->__execute('GET');
    }

    /**
     * This API call will retrieve the unspent transaction outputs (UTXOs) within a wallet.
     * 
     * @param string $prevId    Continue iterating wallets from this prevId as provided by nextBatchPrevId in the previous list
     * @param int $minValue     Ignore unspents smaller than this amount of satoshis
     * @param int $maxValue     Ignore unspents larger than this amount of satoshis
     * @param int $minHeight    Ignore unspents confirmed at a lower block height than the given minHeight
     * @param int $minConfirms  Ignores unspents that have fewer than the given confirmations
     * @return array
     */
    public function listWalletUnspents(string $prevId = null, int $minValue = null, int $maxValue = null, int $minHeight = null, int $minConfirms = null) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/unspents';
        $this->params = [
            'prevId' => $prevId,
            'minValue' => $minValue,
            'maxValue' => $maxValue,
            'minHeight' => $minHeight,
            'minConfirms' => $minConfirms
        ];
        return $this->__execute('GET');
    }

    /**
     * This API call will calculate the total balance across all wallets with the specified coin type.
     * 
     * @return array
     */
    public function getTotalBalances() {
        $this->url = $this->APIEndpoint . '/wallet/balances';
        return $this->__execute('GET');
    }

    /**
     * This API call will retrieve the maximum amount that can be spent with a single transaction from the wallet.
     * 
     * @param int $limit                        Max number of unspents to use (if less than 200)
     * @param int $minValue                     Ignore unspents smaller than this amount of satoshis
     * @param int $maxValue                     Ignore unspents larger than this amount of satoshis
     * @param int $minHeight                    Minimum block height of unspents to fetch
     * @param int $feeRate                      The desired fee rate for the transaction in satoshis/kB
     * @param int $minConfirms                  The required number of confirmations for each non-change unspent
     * @param bool $enforceMinConfirmsForChange Apply the required confirmations set in minConfirms for change outputs
     * @return type
     */
    public function getMaximumSpendable(int $limit = null, int $minValue = null, int $maxValue = null, int $minHeight = null, int $feeRate = null, int $minConfirms = null, bool $enforceMinConfirmsForChange = null) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/maximumSpendable';
        $this->params = [
            'limit' => $limit,
            'minValue' => $minValue,
            'maxValue' => $maxValue,
            'minHeight' => $minHeight,
            'feeRate' => $feeRate,
            'minConfirms' => $minConfirms,
            'enforceMinConfirmsForChange' => $enforceMinConfirmsForChange
        ];
        return $this->__execute('GET');
    }

    /**
     * Prevent all spend activity on a wallet.
     * 
     * @param string $otp   An OTP code for the account obtained using the second factor authentication device
     * @param int $duration Length of time in seconds to freeze spend activity. Defaults to 1 hour.
     * @return array
     */
    public function freezeWallet(string $otp, int $duration = 3600) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/freeze';
        $this->params = [
            'otp' => $otp,
            'duration' => $duration
        ];
        return $this->__execute();
    }

    /**
     * Retrieve a keychain based on its key id.
     * 
     * @param int $limit        Max number of results in a single call. Defaults to 25.
     * @param string $prevId    Continue iterating (provided by nextBatchPrevId in the previous list)
     * @return array
     */
    public function listKeychains(int $limit = 25, string $prevId = null) {
        $this->url = $this->APIEndpoint . '/key';
        $this->params = [
            'limit' => $limit,
            'prevId' => $prevId
        ];
        return $this->__execute('GET');
    }

    /**
     * Retrieve a keychain based on its key id.
     * 
     * @param string $keyId The keychain’s key id.
     * @return array
     */
    public function getKeychain(string $keyId) {
        $this->url = $this->APIEndpoint . '/key/' . $keyId;
        return $this->__execute('GET');
    }

    /**
     * Creates a new keychain on BitGo’s servers and returns the public keychain to the caller.
     * 
     * @param string $source        The origin of the keychain. Must be 'bitgo' for a BitGo key.
     * @param string $enterprise    The enterprise id of the BitGo key (only for ETH)
     * @param bool $newFeeAddress   Create a new keychain instead of fetching enterprise key (only for Ethereum)
     * @return array
     */
    public function createBitGoKeychain(string $source = 'bitgo', string $enterprise = null, bool $newFeeAddress = null) {
        $this->url = $this->APIEndpoint . '/key';
        $this->params = [
            'source' => $source,
            'enterprise' => $this->coin === CurrencyCode::ETHEREUM ? $enterprise : null,
            'newFeeAddress' => $this->coin === CurrencyCode::ETHEREUM ? $newFeeAddress : null
        ];
        return $this->__execute();
    }

    /**
     * Creates a new backup keychain on a third party specializing in key recovery services.
     * 
     * @param string $provider  	The backup provider for the keychain, e. g. 'cme'
     * @param string $source    The origin of the keychain. Must be 'backup' for a backup key.
     * @return array
     */
    public function createBackupKeychain(string $provider, string $source = 'bitgo') {
        $this->url = $this->APIEndpoint . '/key';
        $this->params = [
            'source' => $source,
            'provider' => $provider
        ];
        return $this->__execute();
    }

    /**
     * This API call allows you to add a public keychain on BitGo’s server.
     * 
     * @param string $pub           The keychain’s public key.
     * @param string $encryptedPrv  The keychain’s encrypted private key.
     * @param string $source        The origin of the keychain, e. g. 'bitgo' or 'backup'
     * @return array
     */
    public function addKeychain(string $pub, string $encryptedPrv = null, string $source = 'bitgo') {
        $this->url = $this->APIEndpoint . '/key';
        $this->params = [
            'pub' => $pub,
            'encryptedPrv' => $encryptedPrv,
            'source' => $source
        ];
        return $this->__execute();
    }

    /**
     * Add a rule to a wallet’s policy. A wallet policy’s rules control the conditions under which BitGo will use 
     * its single key to sign a transaction.
     * 
     * @param string $id        The id of the policy rule
     * @param string $type      The type of the policy. See Policy types (https://www.bitgo.com/api/v2/#policy-types)
     * @param string $condition The condition that triggers the policy. See Policy types (https://www.bitgo.com/api/v2/#policy-types)
     * @param string $action    The action to take when the policy is triggered. Should be either 'deny' or 'getapproval'
     * @return array
     */
    public function setVelocityPolicyRule(string $id, string $type, string $condition, string $action) {
        $this->url = $this->APIEndpoint . '/wallet/' . $id . '/policy/rule';
        $this->params = [
            'id' => $id,
            'type' => $type,
            'condition' => $condition,
            'action' => $action
        ];
        return $this->__execute();
    }

    /**
     * Update a rule on a wallet’s policy.
     * 
     * @param string $id        The id of the policy rule to be updated. (This value can’t be changed)
     * @param string $type      The type of the policy. (This value can’t be changed)
     * @param string $condition The new condition that triggers the policy. See Policy types (https://www.bitgo.com/api/v2/#policy-types)
     * @param string $action    The new action to take when the policy is triggered. Should be either 'deny' or 'getapproval'
     * @return array
     */
    public function updateVelocityPolicyRule(string $id, string $type, string $condition, string $action) {
        $this->url = $this->APIEndpoint . '/wallet/' . $id . '/policy/rule';
        $this->params = [
            'id' => $id,
            'type' => $type,
            'condition' => $condition,
            'action' => $action
        ];
        return $this->__execute('PUT');
    }

    /**
     * Remove a policy rule with the specified id. This may require a secondary approval if there is more 
     * than 1 “admin” user on the wallet.
     * 
     * @param string $id        The id of the policy rule to be removed.
     * @param string $type      The type of the policy.
     * @param string $condition The condition that triggers the policy.
     * @param string $action    The action to take when the policy is triggered.
     * @return array
     */
    public function removePolicyRule(string $id, string $type, string $condition, string $action) {
        $this->url = $this->APIEndpoint . '/wallet/' . $id . '/policy/rule';
        $this->params = [
            'id' => $id,
            'type' => $type,
            'condition' => $condition,
            'action' => $action
        ];
        return $this->__execute('DELETE');
    }

    /**
     * This API call is for checking incoming and outgoing wallet shares for the logged-in account.
     * 
     * @return array
     */
    public function listWalletShares() {
        $this->url = $this->APIEndpoint . '/walletshare';
        return $this->__execute('GET');
    }

    /**
     * Gets incoming and outgoing wallet shares for specific share ID.
     * 
     * @param string $shareId   The wallet share Id
     * @return array
     */
    public function getWalletShare(string $shareId) {
        $this->url = $this->APIEndpoint . '/walletshare/' . $shareId;
        return $this->__execute('GET');
    }

    /**
     * Resends the wallet share invitation to the share recipient. The wallet share should not have been accepted yet.
     * 
     * @param string $shareId   The wallet share Id
     * @return array
     */
    public function resendWalletShareInvite(string $shareId) {
        $this->url = $this->APIEndpoint . '/walletshare/' . $shareId . '/resendemail';
        return $this->__execute();
    }

    /**
     * Can be used to cancel a pending outgoing wallet share, or reject an incoming share. The share should not have 
     * been accepted yet.
     * 
     * @param string $shareId   The wallet share Id
     * @return array
     */
    public function cancelWalletShare(string $shareId) {
        $this->url = $this->APIEndpoint . '/walletshare/' . $shareId;
        $this->params = [
            'walletShareId' => $shareId
        ];
        return $this->__execute('DELETE');
    }

    /**
     * In order to revoke the share after they have accepted, you can remove the user from the wallet.
     * 
     * @param string $userId    The user id of the user to remove (can be found on the wallet object)
     * @return array
     */
    public function removeWalletUser(string $userId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/user/' . $userId;
        $this->params = [
            'walletId' => $this->walletId,
            'userId' => $userId
        ];
        return $this->__execute('DELETE');
    }

    /**
     * This API calls retrieves the object information for a pending approval by id.
     * 
     * @param string $pendingApprovalId The pending approval Id
     * @return array
     */
    public function getPendingApproval(string $pendingApprovalId) {
        $this->url = $this->APIEndpoint . '/pendingapprovals/' . $pendingApprovalId;
        return $this->__execute('GET');
    }

    /**
     * List pending approvals on a wallet or an enterprise by providing either a wallet id or an enterprise in the url.
     * 
     * @param string $walletID      Base address of the wallet
     * @param string $enterprise    The public ID of the enterprise
     * @param bool $allTokens       Gets details of all token pending approvals. Only valid for ETH/TETH
     * @return type
     */
    public function listPendingApprovals(string $walletID = null, string $enterprise = null, bool $allTokens = false) {
        $this->url = $this->APIEndpoint . '/pendingapprovals';
        $this->params = [
            'walletID' => $walletID,
            'enterprise' => $this->coin === CurrencyCode::ETHEREUM ? $enterprise : null,
            'allTokens' => $this->coin === CurrencyCode::ETHEREUM || $this->coin === CurrencyCode::ETHEREUM_TESTNET ? $allTokens : null
        ];
        return $this->__execute('GET');
    }

    /**
     * Get webhook payload
     * 
     * @param bool $decodeJson      Set true to decode the JSON
     * @param bool $decodeAsArray   Set true to decode the JSON as array
     * @return type
     */
    public static function getWebhookPayload(bool $decodeJson = true, bool $decodeAsArray = true) {
        if ($decodeJson) {
            return json_decode(file_get_contents('php://input'), $decodeAsArray);
        } else {
            return file_get_contents('php://input');
        }
    }

    /**
     * List all webhooks set up on the wallet.
     * 
     * @param bool $allTokens   Gets details of all token pending approvals. Only valid for ETH/TETH
     * @return array
     */
    public function listWalletWebhooks(bool $allTokens = false) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/webhooks';
        $this->params = [
            'allTokens' => $this->coin === CurrencyCode::ETHEREUM || $this->coin === CurrencyCode::ETHEREUM_TESTNET ? $allTokens : null
        ];
        return $this->__execute('GET');
    }

    /**
     * Add a webhook that will result in an HTTP callback at the specified URL from BitGo when events are triggered.
     * 
     * @param string $url           URL to fire the webhook to.
     * @param string $type          Type of event to listen to (can be 'transfer' or 'pendingaapproval').
     * @param int $numConfirmations Number of confirmations before triggering the webhook. If 0 or unspecified, requests will be sent to the callback endpoint when the transfer is first seen and when it is confirmed.
     * @return array
     */
    public function addWalletWebhook(string $url, string $type, int $numConfirmations = null) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/webhooks';
        $this->params = [
            'url' => $url,
            'type' => $type,
            'numConfirmations' => $numConfirmations
        ];
        return $this->__execute();
    }

    /**
     * Removing a webhook will cause new events of the specified type to no longer trigger HTTP callbacks to your URLs.
     * 
     * @param string $url   URL for callback requests to be made at.
     * @param string $type  Type of event to listen to (can be 'transfer' or 'pendingaapproval').
     * @return array
     */
    public function removeWalletWebhook(string $url, string $type) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/webhooks';
        $this->params = [
            'url' => $url,
            'type' => $type
        ];
        return $this->__execute('DELETE');
    }

    /**
     * This API allows you to simulate and test a webhook so you can view its response.
     * 
     * @param string $webhookId         Webhook ID.
     * @param string $transferId        ID of the transfer to be used in the simulation.
     * @param string $pendingApprovalId ID of the pending approval to be used in the simulation.
     * @return array
     */
    public function simulateWalletWebhook(string $webhookId, string $transferId = null, string $pendingApprovalId = null) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/webhooks/' . $webhookId . '/simulate';
        $this->params = [
            'webhookId' => $webhookId,
            'transferId' => $transferId,
            'pendingApprovalId' => $pendingApprovalId
        ];
        return $this->__execute();
    }

    /**
     * List all webhooks set up on the user.
     * 
     * @return array
     */
    public function listUserWebhooks() {
        $this->url = $this->APIEndpoint . '/webhooks';
        return $this->__execute('GET');
    }

    /**
     * Add a webhook that will result in an HTTP callback at the specified URL from BitGo when events are triggered.
     * 
     * @param string $url           URL to fire the webhook to.
     * @param string $type          Type of event to listen to (can be of type 'block').
     * @param string $label         Label of the new webhook.
     * @param int $numConfirmations Number of confirmations before triggering the webhook.
     * @return array
     */
    public function addUserWebhook(string $url, string $type, string $label = null, int $numConfirmations = null) {
        $this->url = $this->APIEndpoint . '/webhooks';
        $this->params = [
            'url' => $url,
            'type' => $type,
            'label' => $label,
            'numConfirmations' => $numConfirmations
        ];
        return $this->__execute();
    }

    /**
     * Removing a webhook will cause new events of the specified type to no longer trigger HTTP callbacks to your URLs.
     * 
     * @param string $url   URL for callback requests to be made at.
     * @param string $type  Type of event to listen to (can be of type 'block').
     * @return array
     */
    public function removeUserWebhook(string $url, string $type) {
        $this->url = $this->APIEndpoint . '/webhooks';
        $this->params = [
            'url' => $url,
            'type' => $type
        ];
        return $this->__execute('DELETE');
    }

    /**
     * This API allows you to simulate and test a webhook so you can view its response.
     * 
     * @param string $webhookId
     * @return array
     */
    public function simulateUserWebhook(string $webhookId, string $blockId = null) {
        $this->url = $this->APIEndpoint . '/webhooks/' . $webhookId . '/simulate';
        $this->params = [
            'webhookId' => $webhookId,
            'blockId' => $blockId
        ];
        return $this->__execute();
    }

    /**
     * Adds a webhook that will result in an HTTP callback at the specified URL from BitGo when events are triggered.
     * 
     * @param string $url           URL to fire the webhook to.
     * @param string $type          Type of event to listen to (can be 'block' or 'wallet_confirmation').
     * @param string $label         Label of the new webhook.
     * @param int $numConfirmations Number of confirmations before triggering the webhook. If 0 or unspecified, requests will be sent to the callback endpoint when the transfer is first seen and when it is confirmed.

     * @return array
     */
    public function addBlockWebhook(string $url, string $type = 'block', string $label = null, int $numConfirmations = null) {
        $this->url = $this->APIEndpoint . '/webhooks';
        $this->params = [
            'url' => $url,
            'type' => (in_array($type, ['block', 'wallet_confirmation'])) ? $type : 'block',
            'label' => $label,
            'numConfirmations' => $numConfirmations
        ];
        return $this->__execute();
    }

    /**
     * Returns block webhooks. The types of webhooks are block and wallet_confirmation notifications.
     * 
     * @return array
     */
    public function listBlockWebhooks() {
        $this->url = $this->APIEndpoint . '/webhooks';
        return $this->__execute('GET');
    }

    /**
     * Removing a webhook will cause new events of the specified type to no longer trigger HTTP callbacks to your URLs.
     * 
     * @param string $url
     * @param string $type
     * @return array
     */
    public function removeBlockWebhook(string $url, string $type = 'block') {
        $this->url = $this->APIEndpoint . '/webhooks';
        $this->params = [
            'url' => $url,
            'type' => (in_array($type, ['block', 'wallet_confirmation'])) ? $type : 'block'
        ];
        return $this->__execute('DELETE');
    }

    /**
     * This API is still being developed. BitGo will update this section soon.
     * 
     * @return array
     */
    public function getMarketPriceData() {
        $this->url = $this->APIEndpoint . '/market/latest';
        return $this->__execute('GET');
    }

    /**
     * Returns the recommended fee rate per kilobyte to confirm a transaction within a target number of blocks.
     * 
     * @param int $numBlocks    The target number of blocks for the transaction to be confirmed. The accepted range is 1 - 1000 and the default value is 2.
     * @return array
     */
    public function estimateTransactionFees(int $numBlocks = null) {
        $this->url = $this->APIEndpoint . '/tx/fee';
        $this->params = [
            'numBlocks' => $numBlocks
        ];
        return $this->__execute('GET');
    }

    private function __execute(string $requestType = 'POST') {
        $ch = curl_init($this->url);
        if ($requestType === 'GET') {
            curl_setopt($ch, CURLOPT_URL, $this->url . '?' . http_build_query(array_filter($this->params)));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        } elseif ($requestType === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array_filter($this->params)));
        } elseif ($requestType === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array_filter($this->params)));
        } elseif ($requestType === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array_filter($this->params)));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken
        ]);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

}
