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

class BitGoSDK implements BitGoSDKInterface {

    const BITGO_PRODUCTION_API_ENDPOINT = 'https://www.bitgo.com/api/v2/';
    const BITGO_TESTNET_API_ENDPOINT = 'https://test.bitgo.com/api/v2/';

    private $APIEndpoint = null;
    private $url = null;
    private $params = [];
    private $allowedCoins = ['btc', 'bch', 'btg', 'eth', 'ltc', 'xrp', 'rmg', 'erc', 'omg', 'zrx', 'fun', 'gnt', 'rep', 'bat', 'knc', 'cvc', 'eos', 'qrl', 'nmr', 'pay', 'brd', 'tbtc', 'tbch', 'teth', 'tltc', 'txrp', 'trmg', 'terc'];

    /**
     * @param string $accessToken
     * @param string $coin
     * @param bool $testNet
     * @throws \Exception
     */
    public function __construct(string $accessToken, string $coin = 'btc', bool $testNet = false) {
        $this->accessToken = $accessToken;
        $this->coin = $coin;
        $this->testNet = $testNet;
        $this->APIEndpoint = (!$this->testNet) ? self::BITGO_PRODUCTION_API_ENDPOINT . $this->coin : self::BITGO_TESTNET_API_ENDPOINT . $this->coin;

        if (!in_array($this->coin, $this->allowedCoins)) {
            throw new \Exception('You are trying to use an invalid coin!');
        }
    }

    /**
     * This API call lists all of a user’s wallets for a given coin.
     * 
     * @return array
     */
    public function listWallets() {
        $this->url = $this->APIEndpoint . '/wallet';
        return $this->__execute('GET');
    }

    /**
     * This API call retrieves wallet object information by the wallet ID.
     * 
     * @param string $walletId
     * @return array
     */
    public function getWallet(string $walletId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId;
        return $this->__execute('GET');
    }

    /**
     * This API call retrieves wallet object information by an address belonging to the wallet.
     * 
     * @param string $walletAddress
     * @return array
     */
    public function getWalletByAddress(string $walletAddress) {
        $this->url = $this->APIEndpoint . '/wallet/address/' . $walletAddress;
        return $this->__execute('GET');
    }

    /**
     * Retrieves a list of transfers, which correspond to the deposits and withdrawals of digital currency on a wallet.
     * 
     * @param string $walletId
     * @return array
     */
    public function listWalletTransfers(string $walletId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/transfer';
        return $this->__execute('GET');
    }

    /**
     * Wallet transfers represent digital currency sends and receives on your wallet.
     * 
     * @param string $walletId
     * @param string $transactionId
     * @return array
     */
    public function getWalletTransfer(string $walletId, string $transactionId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/transfer/' . $transactionId;
        return $this->__execute('GET');
    }

    /**
     * Get the transaction on a wallet sequence ID that was passed in when sending a transaction 
     * (via Send Transaction or Send Transaction to Many).
     * 
     * @param string $walletId
     * @param string $sequenceId
     * @return array
     */
    public function getWalletTransferBySequenceId(string $walletId, string $sequenceId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/transfer/sequenceId/' . $sequenceId;
        return $this->__execute('GET');
    }

    /**
     * This API call is used to create a new receive address for your wallet, which enhances your privacy.
     * 
     * @param string $walletId
     * @return array
     */
    public function createWalletAddress(string $walletId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/address';
        $this->params = [
            'id' => $walletId
        ];
        return $this->__execute('POST');
    }

    /**
     * This API call is used to get information about a single wallet address.
     * 
     * @param string $walletId
     * @param string $addressOrId
     * @return array
     */
    public function getWalletAddress(string $walletId, string $addressOrId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/address/' . $addressOrId;
        return $this->__execute('GET');
    }

    /**
     * This API call is used to update fields on a wallet address.
     * 
     * @param string $walletId
     * @param string $addressOrId
     * @param string $label
     * @return array
     */
    public function updateWalletAddress(string $walletId, string $addressOrId, string $label) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/address/' . $addressOrId;
        $this->params = [
            'label' => $label
        ];
        return $this->__execute('PUT');
    }

    /**
     * This API call is used to retrieve a wallet’s addresses.
     * 
     * @param string $walletId
     * @return array
     */
    public function getWalletAddresses(string $walletId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/addresses';
        return $this->__execute('GET');
    }

    /**
     * This API call lists all blockchain transactions for a given wallet.
     * 
     * @param string $walletId
     * @return array
     */
    public function getWalletTransactions(string $walletId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/tx';
        return $this->__execute('GET');
    }

    /**
     * This API call retrieves object information for a specific transaction hash by specifying the transaction ID.
     * 
     * @param string $walletId
     * @param string $transactionId
     * @return array
     */
    public function getWalletTransaction(string $walletId, string $transactionId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/tx/' . $transactionId;
        return $this->__execute('GET');
    }

    /**
     * This API call will retrieve the unspent transaction outputs (UTXOs) within a wallet.
     * 
     * @param string $walletId
     * @return array
     */
    public function listWalletUnspents(string $walletId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/unspents';
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
     * @param string $walletId
     * @return array
     */
    public function getMaximumSpendable(string $walletId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/maximumSpendable';
        return $this->__execute('GET');
    }

    /**
     * Prevent all spend activity on a wallet.
     * 
     * @param string $walletId
     * @param int $duration
     * @param string $otp
     * @return array
     */
    public function freezeWallet(string $walletId, int $duration, string $otp) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/freeze';
        $this->params = [
            'duration' => $duration,
            'otp' => $otp
        ];
        return $this->__execute('POST');
    }

    /**
     * Retrieve a keychain based on its key id.
     * 
     * @return array
     */
    public function listKeychains() {
        $this->url = $this->APIEndpoint . '/key';
        return $this->__execute('GET');
    }

    /**
     * Retrieve a keychain based on its key id.
     * 
     * @param string $keyId
     * @return array
     */
    public function getKeychain(string $keyId) {
        $this->url = $this->APIEndpoint . '/key/' . $keyId;
        return $this->__execute('GET');
    }

    /**
     * Creates a new keychain on BitGo’s servers and returns the public keychain to the caller.
     * 
     * @param string $enterprise
     * @param string $source
     * @return array
     */
    public function createBitGoKeychain(string $enterprise, string $source = 'bitgo') {
        $this->url = $this->APIEndpoint . '/key';
        $this->params = [
            'source' => $source,
            'enterprise' => $enterprise
        ];
        return $this->__execute('POST');
    }

    /**
     * Creates a new backup keychain on a third party specializing in key recovery services.
     * 
     * @param string $provider
     * @param string $source
     * @return array
     */
    public function createBackupKeychain(string $provider, string $source = 'bitgo') {
        $this->url = $this->APIEndpoint . '/key';
        $this->params = [
            'source' => $source,
            'provider' => $provider
        ];
        return $this->__execute('POST');
    }

    /**
     * This API call allows you to add a public keychain on BitGo’s server.
     * 
     * @param string $pub
     * @param string $encryptedPrv
     * @param string $source
     * @return array
     */
    public function addKeychain(string $pub, string $encryptedPrv = '', string $source = 'bitgo') {
        $this->url = $this->APIEndpoint . '/key';
        $this->params = [
            'pub' => $pub,
            'encryptedPrv' => $encryptedPrv,
            'source' => $source
        ];
        return $this->__execute('POST');
    }

    /**
     * Add a rule to a wallet’s policy. A wallet policy’s rules control the conditions under which BitGo will use 
     * its single key to sign a transaction.
     * 
     * @param string $id
     * @param string $type
     * @param string $condition
     * @param string $action
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
        return $this->__execute('POST');
    }

    /**
     * Update a rule on a wallet’s policy.
     * 
     * @param string $id
     * @param string $type
     * @param string $condition
     * @param string $action
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
     * @param string $id
     * @param string $type
     * @param string $condition
     * @param string $action
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
     * @param string $shareId
     * @return array
     */
    public function getWalletShare(string $shareId) {
        $this->url = $this->APIEndpoint . '/walletshare/' . $shareId;
        return $this->__execute('GET');
    }

    /**
     * Resends the wallet share invitation to the share recipient. The wallet share should not have been accepted yet.
     * 
     * @param string $shareId
     * @return array
     */
    public function resendWalletShareInvite(string $shareId) {
        $this->url = $this->APIEndpoint . '/walletshare/' . $shareId . '/resendemail';
        return $this->__execute('POST');
    }

    /**
     * Can be used to cancel a pending outgoing wallet share, or reject an incoming share. The share should not have 
     * been accepted yet.
     * 
     * @param string $shareId
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
     * @param string $walletId
     * @param string $userId
     * @return array
     */
    public function removeWalletUser(string $walletId, string $userId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/user/' . $userId;
        $this->params = [
            'walletId' => $walletId,
            'userId' => $userId
        ];
        return $this->__execute('DELETE');
    }

    /**
     * This API calls retrieves the object information for a pending approval by id.
     * 
     * @param string $pendingApprovalId
     * @return array
     */
    public function getPendingApproval(string $pendingApprovalId) {
        $this->url = $this->APIEndpoint . '/pendingapprovals/' . $pendingApprovalId;
        return $this->__execute('GET');
    }

    /**
     * List pending approvals on a wallet or an enterprise by providing either a wallet id or an enterprise in the url.
     * 
     * @return array
     */
    public function listPendingApprovals() {
        $this->url = $this->APIEndpoint . '/pendingapprovals';
        return $this->__execute('GET');
    }

    /**
     * List all webhooks set up on the wallet.
     * 
     * @param string $walletId
     * @return array
     */
    public function listWalletWebhooks(string $walletId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/webhooks';
        return $this->__execute('GET');
    }

    /**
     * Add a webhook that will result in an HTTP callback at the specified URL from BitGo when events are triggered.
     * 
     * @param string $walletId
     * @param string $url
     * @param string $type
     * @return array
     */
    public function addWalletWebhook(string $walletId, string $url, string $type) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/webhooks';
        $this->params = [
            'url' => $url,
            'type' => $type
        ];
        return $this->__execute('POST');
    }

    /**
     * Removing a webhook will cause new events of the specified type to no longer trigger HTTP callbacks to your URLs.
     * 
     * @param string $walletId
     * @param string $url
     * @param string $type
     * @return array
     */
    public function removeWalletWebhook(string $walletId, string $url, string $type) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/webhooks';
        $this->params = [
            'url' => $url,
            'type' => $type
        ];
        return $this->__execute('DELETE');
    }

    /**
     * This API allows you to simulate and test a webhook so you can view its response.
     * 
     * @param string $walletId
     * @param string $webhookId
     * @return array
     */
    public function simulateWalletWebhook(string $walletId, string $webhookId) {
        $this->url = $this->APIEndpoint . '/wallet/' . $walletId . '/webhooks/' . $webhookId . '/simulate';
        $this->params = [
            'webhookId' => $webhookId
        ];
        return $this->__execute('POST');
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
     * @param string $url
     * @param string $type
     * @return array
     */
    public function addUserWebhook(string $url, string $type) {
        $this->url = $this->APIEndpoint . '/webhooks';
        $this->params = [
            'url' => $url,
            'type' => $type
        ];
        return $this->__execute('POST');
    }

    /**
     * Removing a webhook will cause new events of the specified type to no longer trigger HTTP callbacks to your URLs.
     * 
     * @param string $url
     * @param string $type
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
    public function simulateUserWebhook(string $webhookId) {
        $this->url = $this->APIEndpoint . '/webhooks/' . $webhookId . '/simulate';
        $this->params = [
            'webhookId' => $webhookId
        ];
        return $this->__execute('POST');
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
     * @return array
     */
    public function estimateTransactionFees() {
        $this->url = $this->APIEndpoint . '/tx/fee';
        return $this->__execute('GET');
    }

    private function __execute(string $requestType = 'POST') {
        $ch = curl_init($this->url);
        if ($requestType === 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        } elseif ($requestType === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->params));
        } elseif ($requestType === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } elseif ($requestType === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->params));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $this->accessToken]);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

}
