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

namespace neto737\BitGoSDK;

use neto737\BitGoSDK\Enum\CurrencyCode;
use neto737\BitGoSDK\Enum\AddressType;

class BitGoSDK
{
    const BITGO_PRODUCTION_API_ENDPOINT = 'https://app.bitgo.com/api/v2/';
    const BITGO_TESTNET_API_ENDPOINT = 'https://app.bitgo-test.com/api/v2/';
    const DEFAULT_TIMEOUT = 60;
    const DEFAULT_CONNECT_TIMEOUT = 10;

    // Coin families for coin-specific params. Each lists mainnet and testnet codes, so testnet wallets get the same params.
    private const ETH_COINS = ['eth', 'teth', 'gteth', 'hteth', 'sepeth'];
    private const XRP_COINS = ['xrp', 'txrp'];
    private const XLM_COINS = ['xlm', 'txlm'];

    private $APIEndpoint = null;
    private $AuthAPIEndpoint = null;
    private $url = null;
    private $params = [];
    public $walletId = null;
    private $requestIP = null;
    public string $accessToken;
    public string $coin;
    public bool $testNet;
    private int $timeout = self::DEFAULT_TIMEOUT;
    private int $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT;
    private bool $forceIPv4 = false;

    /**
     * BitGoSDK Initialization
     *
     * @param string $accessToken   Set your Access Token (API Key)
     * @param string $coin          Select the coin you want to use with the BitGoSDK (use CurrencyCode class to select)
     * @param bool $testNet         Start instance as testnet (true or false), default is false
     * @throws \Exception
     */
    public function __construct(string $accessToken, string $coin = CurrencyCode::BITCOIN, bool $testNet = false)
    {
        $this->accessToken = $accessToken;
        $this->coin = $coin;
        $this->testNet = $testNet;
        $this->APIEndpoint = (!$this->testNet) ? self::BITGO_PRODUCTION_API_ENDPOINT . $this->coin : self::BITGO_TESTNET_API_ENDPOINT . $this->coin;
        $this->AuthAPIEndpoint = (!$this->testNet) ? self::BITGO_PRODUCTION_API_ENDPOINT : self::BITGO_TESTNET_API_ENDPOINT;
    }

    /**
     * Set the local IP that requests to BitGo are sent from (useful on hosts with several IPs, such as shared hosting)
     *
     * @param string $ipAddress An IPv4 or IPv6 address of this host
     * @return bool             Whether the IP was applied. It is not applied on testnet, or if $ipAddress is not a valid IP,
     *                          and any IP set earlier is kept.
     */
    public function setRequestIP(string $ipAddress)
    {
        if ($this->testNet || filter_var($ipAddress, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        $this->requestIP = $ipAddress;
        return true;
    }

    /**
     * Set the maximum time a request may take, connection included
     *
     * @param int $seconds  0 waits forever
     * @throws \InvalidArgumentException
     */
    public function setTimeout(int $seconds): void
    {
        if ($seconds < 0) {
            throw new \InvalidArgumentException('Timeout must not be negative.');
        }
        $this->timeout = $seconds;
    }

    /**
     * Set the maximum time to wait for a connection to BitGo
     *
     * @param int $seconds  0 uses cURL's built-in default of 300 seconds
     * @throws \InvalidArgumentException
     */
    public function setConnectTimeout(int $seconds): void
    {
        if ($seconds < 0) {
            throw new \InvalidArgumentException('Connect timeout must not be negative.');
        }
        $this->connectTimeout = $seconds;
    }

    /**
     * Resolve hostnames to IPv4 addresses only. Off by default, so cURL uses IPv4 or IPv6 as the system allows.
     *
     * @param bool $force
     */
    public function setForceIPv4(bool $force): void
    {
        $this->forceIPv4 = $force;
    }

    /**
     * Converts value in satoshi to BTC value
     *
     * @param int|string $amount    amount in satoshi that you want to convert to BTC
     * @return string               value converted to BTC, with 8 decimal places
     * @throws \InvalidArgumentException
     */
    public static function toBTC($amount)
    {
        return self::fromBaseUnits($amount, 8);
    }

    /**
     * Converts value in BTC to satoshi value
     *
     * @param float|int|string $amount  amount in BTC that you want to convert to satoshi. Pass a string to avoid float rounding.
     * @return int                      value converted to satoshi
     * @throws \InvalidArgumentException
     */
    public static function toSatoshi($amount)
    {
        if (is_float($amount)) {
            $amount = sprintf('%.8F', $amount);
        }
        return (int) self::toBaseUnits((string) $amount, 8);
    }

    /**
     * Converts a decimal amount to an integer amount of base units, e.g. ETH to wei with 18 decimals.
     *
     * @param string $amount    decimal amount, e.g. '1.5'
     * @param int $decimals     number of decimal places of the coin, e.g. 8 for BTC or 18 for ETH
     * @return string           amount in base units, e.g. '1500000000000000000'
     * @throws \InvalidArgumentException if the amount is not a decimal number or has more than $decimals decimal places
     */
    public static function toBaseUnits(string $amount, int $decimals): string
    {
        if ($decimals < 0) {
            throw new \InvalidArgumentException('Decimals must not be negative.');
        }
        if (!preg_match('/^(-?)(\d*)(?:\.(\d*))?$/', $amount, $matches) || $matches[2] . ($matches[3] ?? '') === '') {
            throw new \InvalidArgumentException("Amount '$amount' is not a decimal number.");
        }

        $fraction = rtrim($matches[3] ?? '', '0');
        if (strlen($fraction) > $decimals) {
            throw new \InvalidArgumentException("Amount '$amount' has more than $decimals decimal places.");
        }

        $units = ltrim($matches[2] . str_pad($fraction, $decimals, '0'), '0');
        return $units === '' ? '0' : $matches[1] . $units;
    }

    /**
     * Converts an integer amount of base units to a decimal amount, e.g. wei to ETH with 18 decimals.
     *
     * @param int|string|float $amount  amount in base units, e.g. '1500000000000000000'. A float is accepted only if it is a whole number.
     * @param int $decimals             number of decimal places of the coin, e.g. 8 for BTC or 18 for ETH
     * @return string                   decimal amount with $decimals decimal places, e.g. '1.500000000000000000'
     * @throws \InvalidArgumentException if the amount is not an integer
     */
    public static function fromBaseUnits($amount, int $decimals): string
    {
        if ($decimals < 0) {
            throw new \InvalidArgumentException('Decimals must not be negative.');
        }
        if (is_float($amount) && is_finite($amount) && floor($amount) === $amount && abs($amount) <= 2 ** 53) {
            $amount = sprintf('%.0F', $amount);
        }
        if (!(is_int($amount) || is_string($amount)) || !preg_match('/^(-?)(\d+)$/', (string) $amount, $matches)) {
            throw new \InvalidArgumentException('Amount must be an integer number of base units, as an int or a string of digits. Use toBaseUnits() to convert a decimal amount.');
        }

        $units = ltrim($matches[2], '0');
        if ($units === '') {
            return $decimals === 0 ? '0' : '0.' . str_repeat('0', $decimals);
        }
        if ($decimals > 0) {
            $units = str_pad($units, $decimals + 1, '0', STR_PAD_LEFT);
            $units = substr($units, 0, -$decimals) . '.' . substr($units, -$decimals);
        }
        return $matches[1] . $units;
    }

    /**
     * This API call retrieves information about the current authenticated user.
     *
     * @return array
     */
    public function getCurrentUserProfile()
    {
        $this->url = $this->AuthAPIEndpoint . 'user/me';
        return $this->__execute('GET');
    }

    /**
     * This API call retrieves information about the current session access token.
     *
     * @return array
     */
    public function getSessionInfo()
    {
        $this->url = $this->AuthAPIEndpoint . 'user/session';
        return $this->__execute('GET');
    }

    /**
     * Locks the current session, preventing future automated sends without a subsequent unlock (OTP will be required for the unlock).
     *
     * @return array
     */
    public function lockSession()
    {
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
    public function unlockSession(string $otp, int $duration = 600)
    {
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
    public function logout()
    {
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
    public function listWallets(int $limit = 25, ?string $prevId = null, bool $allTokens = false)
    {
        $this->url = $this->APIEndpoint . '/wallet';
        $this->params = [
            'limit' => $limit,
            'prevId' => $prevId,
            'allTokens' => $this->coinIn(self::ETH_COINS) ? $allTokens : null,
        ];
        return $this->__execute('GET');
    }

    /**
     * This API call retrieves wallet object information by the wallet ID.
     *
     * @param bool $allTokens   Gets details of all tokens associated with this wallet. Only valid for ETH/TETH
     * @return array
     */
    public function getWallet(bool $allTokens = false)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId;
        $this->params = [
            'allTokens' => $this->coinIn(self::ETH_COINS) ? $allTokens : null,
        ];
        return $this->__execute('GET');
    }

    /**
     * This API call retrieves wallet object information by an address belonging to the wallet.
     *
     * @param string $walletAddress The address
     * @return array
     */
    public function getWalletByAddress(string $walletAddress)
    {
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
    public function listWalletTransfers(?string $prevId = null, bool $allTokens = false)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/transfer';
        $this->params = [
            'prevId' => $prevId,
            'allTokens' => $this->coinIn(self::ETH_COINS) ? $allTokens : null,
        ];
        return $this->__execute('GET');
    }

    /**
     * Wallet transfers represent digital currency sends and receives on your wallet.
     *
     * @param string $transactionId ID of the wallet transfer
     * @return array
     */
    public function getWalletTransfer(string $transactionId)
    {
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
    public function getWalletTransferBySequenceId(string $sequenceId)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/transfer/sequenceId/' . $sequenceId;
        return $this->__execute('GET');
    }

    /**
     * This API call is used to create a new receive address for your wallet, which enhances your privacy.
     *
     * @param int $chain            The address chain, see AddressType. Defaults to 10 (AddressType::P2SH_P2WSH_DEPOSIT, SegWit wrapped in P2SH).
     *                              Pass AddressType::LEGACY_DEPOSIT (0) for coins without SegWit support, such as BCH.
     * @param string $label         Human-readable name for the address
     * @param bool $lowPriority     Whether the deployment of the address forwarder contract should use a low priority fee key (ETH only)
     * @param int $gasPrice         Custom gas price to be used for deployment of receive addresses (ETH only)
     * @return array
     */
    public function createWalletAddress(int $chain = AddressType::P2SH_P2WSH_DEPOSIT, ?string $label = null, bool $lowPriority = false, ?int $gasPrice = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/address';
        $this->params = [
            'chain' => $chain,
            'label' => $label,
            'lowPriority' => $this->coinIn(self::ETH_COINS) ? $lowPriority : null,
            'gasPrice' => $this->coinIn(self::ETH_COINS) ? $gasPrice : null,
        ];
        return $this->__execute();
    }

    /**
     * This API call is used to get information about a single wallet address.
     *
     * @param string $addressOrId   Valid address or address id of the wallet.
     * @return array
     */
    public function getWalletAddress(string $addressOrId, ?string $dt = null, ?string $memoId = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/address/' . $addressOrId;
        $this->params = [
            'dt' => $this->coinIn(self::XRP_COINS) ? $dt : null,
            'memoId' => $this->coinIn(self::XLM_COINS) ? $memoId : null
        ];
        return $this->__execute('GET');
    }

    /**
     * This API call is used to update fields on a wallet address.
     *
     * @param string $addressOrId   Valid address or address id of the wallet.
     * @param string $label         Human-readable name for the address
     * @return array
     */
    public function updateWalletAddress(string $addressOrId, ?string $dt = null, ?string $memoId = null, ?string $label = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/address/' . $addressOrId;
        $this->params = [
            'dt' => $this->coinIn(self::XRP_COINS) ? $dt : null,
            'memoId' => $this->coinIn(self::XLM_COINS) ? $memoId : null,
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
    public function getWalletAddresses(?string $labelContains = null, ?int $limit = null, ?bool $mine = null, ?string $prevId = null, array $chains = [], ?int $sort = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/addresses';
        $this->params = [
            'labelContains' => $labelContains,
            'limit' => $limit,
            'mine' => $mine,
            'prevId' => $prevId,
            'chains' => $chains,
            'sort' => $sort
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
    public function getWalletTransactions(?string $prevId = null, bool $allTokens = false)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/tx';
        $this->params = [
            'prevId' => $prevId,
            'allTokens' => $this->coinIn(self::ETH_COINS) ? $allTokens : null,
        ];
        return $this->__execute('GET');
    }

    /**
     * This API call retrieves object information for a specific transaction hash by specifying the transaction ID.
     *
     * @param string $transactionId Transaction hash to retrieve.
     * @return array
     */
    public function getWalletTransaction(string $transactionId)
    {
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
    public function listWalletUnspents(?string $prevId = null, ?int $minValue = null, ?int $maxValue = null, ?int $minHeight = null, ?int $minConfirms = null)
    {
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
    public function getTotalBalances()
    {
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
     * @return array<string, mixed>
     */
    public function getMaximumSpendable(?int $limit = null, ?int $minValue = null, ?int $maxValue = null, ?int $minHeight = null, ?int $feeRate = null, ?int $minConfirms = null, ?bool $enforceMinConfirmsForChange = null)
    {
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
    public function freezeWallet(string $otp, int $duration = 3600)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/freeze';
        $this->params = [
            'otp' => $otp,
            'duration' => $duration
        ];
        return $this->__execute();
    }

    /**
     * List the current user's keychains for this coin.
     *
     * @param int $limit        Max number of results in a single call. Defaults to 25.
     * @param string $prevId    Continue iterating (provided by nextBatchPrevId in the previous list)
     * @return array
     */
    public function listKeychains(int $limit = 25, ?string $prevId = null)
    {
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
    public function getKeychain(string $keyId)
    {
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
    public function createBitGoKeychain(string $source = 'bitgo', ?string $enterprise = null, ?bool $newFeeAddress = null)
    {
        $this->url = $this->APIEndpoint . '/key';
        $this->params = [
            'source' => $source,
            'enterprise' => $this->coinIn(self::ETH_COINS) ? $enterprise : null,
            'newFeeAddress' => $this->coinIn(self::ETH_COINS) ? $newFeeAddress : null
        ];
        return $this->__execute();
    }

    /**
     * Creates a new backup keychain on a third party specializing in key recovery services.
     *
     * @param string $provider  The backup provider for the keychain, e. g. 'cme'
     * @param string $source    The origin of the keychain. Must be 'backup' for a backup key.
     * @return array
     */
    public function createBackupKeychain(string $provider, string $source = 'bitgo')
    {
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
    public function addKeychain(string $pub, ?string $encryptedPrv = null, string $source = 'bitgo')
    {
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
     * @param string $type      The type of the policy. See the policy docs (https://developers.bitgo.com/docs/policies-overview)
     * @param string $condition The condition that triggers the policy. See the policy docs (https://developers.bitgo.com/docs/policies-overview)
     * @param string $action    The action to take when the policy is triggered. Should be either 'deny' or 'getapproval'
     * @return array
     */
    public function setVelocityPolicyRule(string $id, string $type, string $condition, string $action)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/policy/rule';
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
     * @param string $condition The new condition that triggers the policy. See the policy docs (https://developers.bitgo.com/docs/policies-overview)
     * @param string $action    The new action to take when the policy is triggered. Should be either 'deny' or 'getapproval'
     * @return array
     */
    public function updateVelocityPolicyRule(string $id, string $type, string $condition, string $action)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/policy/rule';
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
    public function removePolicyRule(string $id, string $type, string $condition, string $action)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/policy/rule';
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
    public function listWalletShares()
    {
        $this->url = $this->APIEndpoint . '/walletshare';
        return $this->__execute('GET');
    }

    /**
     * Gets incoming and outgoing wallet shares for specific share ID.
     *
     * @param string $shareId   The wallet share Id
     * @return array
     */
    public function getWalletShare(string $shareId)
    {
        $this->url = $this->APIEndpoint . '/walletshare/' . $shareId;
        return $this->__execute('GET');
    }

    /**
     * Resends the wallet share invitation to the share recipient. The wallet share should not have been accepted yet.
     *
     * @param string $shareId   The wallet share Id
     * @return array
     */
    public function resendWalletShareInvite(string $shareId)
    {
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
    public function cancelWalletShare(string $shareId)
    {
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
    public function removeWalletUser(string $userId)
    {
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
    public function getPendingApproval(string $pendingApprovalId)
    {
        $this->url = $this->APIEndpoint . '/pendingapprovals/' . $pendingApprovalId;
        return $this->__execute('GET');
    }

    /**
     * List pending approvals on a wallet or an enterprise by providing either a wallet id or an enterprise in the url.
     *
     * @param string $walletID      Base address of the wallet
     * @param string $enterprise    The public ID of the enterprise
     * @param bool $allTokens       Gets details of all token pending approvals. Only valid for ETH/TETH
     * @return array<string, mixed>
     */
    public function listPendingApprovals(?string $walletID = null, ?string $enterprise = null, bool $allTokens = false)
    {
        $this->url = $this->APIEndpoint . '/pendingapprovals';
        $this->params = [
            'walletID' => $walletID,
            'enterprise' => $this->coinIn(self::ETH_COINS) ? $enterprise : null,
            'allTokens' => $this->coinIn(self::ETH_COINS) ? $allTokens : null
        ];
        return $this->__execute('GET');
    }

    /**
     * Get webhook payload
     *
     * @param bool $decodeJson      Set true to decode the JSON
     * @param bool $decodeAsArray   Set true to decode the JSON as array
     * @return mixed                The decoded payload, or the raw body if $decodeJson is false
     */
    public static function getWebhookPayload(bool $decodeJson = true, bool $decodeAsArray = true)
    {
        if ($decodeJson) {
            return json_decode(file_get_contents('php://input'), $decodeAsArray);
        } else {
            return file_get_contents('php://input');
        }
    }

    /**
     * List all webhooks set up on the wallet.
     *
     * @param bool $allTokens   Gets details of all token webhooks. Only valid for ETH/TETH
     * @return array
     */
    public function listWalletWebhooks(bool $allTokens = false)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/webhooks';
        $this->params = [
            'allTokens' => $this->coinIn(self::ETH_COINS) ? $allTokens : null
        ];
        return $this->__execute('GET');
    }

    /**
     * Add a webhook that will result in an HTTP callback at the specified URL from BitGo when events are triggered.
     *
     * @param string $url           URL to fire the webhook to.
     * @param string $type          Type of event to listen to (can be 'transfer' or 'pendingapproval').
     * @param int $numConfirmations Number of confirmations before triggering the webhook. If 0 or unspecified, requests will be sent to the callback endpoint when the transfer is first seen and when it is confirmed.
     * @return array
     */
    public function addWalletWebhook(string $url, string $type, ?int $numConfirmations = null)
    {
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
     * @param string $type  Type of event to listen to (can be 'transfer' or 'pendingapproval').
     * @return array
     */
    public function removeWalletWebhook(string $url, string $type)
    {
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
    public function simulateWalletWebhook(string $webhookId, ?string $transferId = null, ?string $pendingApprovalId = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/webhooks/' . $webhookId . '/simulate';
        $this->params = [
            'webhookId' => $webhookId,
            'transferId' => $transferId,
            'pendingApprovalId' => $pendingApprovalId
        ];
        return $this->__execute();
    }

    /**
     * List the user's webhooks for this coin, such as block and wallet_confirmation webhooks.
     *
     * @return array
     */
    public function listUserWebhooks()
    {
        $this->url = $this->APIEndpoint . '/webhooks';
        return $this->__execute('GET');
    }

    /**
     * Add a webhook that will result in an HTTP callback at the specified URL from BitGo when events are triggered.
     *
     * @param string $url           URL to fire the webhook to.
     * @param string $type          Type of event to listen to, e.g. 'block' or 'wallet_confirmation'.
     * @param string $label         Label of the new webhook.
     * @param int $numConfirmations Number of confirmations before triggering the webhook.
     * @return array
     */
    public function addUserWebhook(string $url, string $type, ?string $label = null, ?int $numConfirmations = null)
    {
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
     * @param string $type  Type of event to listen to, e.g. 'block' or 'wallet_confirmation'.
     * @return array
     */
    public function removeUserWebhook(string $url, string $type)
    {
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
    public function simulateUserWebhook(string $webhookId, ?string $blockId = null)
    {
        $this->url = $this->APIEndpoint . '/webhooks/' . $webhookId . '/simulate';
        $this->params = [
            'webhookId' => $webhookId,
            'blockId' => $blockId
        ];
        return $this->__execute();
    }

    /**
     * @deprecated 2.3.0 Same endpoint as addUserWebhook(). Use addUserWebhook(), which sends $type unchanged.
     *
     * @param string $url           URL to fire the webhook to.
     * @param string $type          'block' or 'wallet_confirmation'. Any other value is sent as 'block'.
     * @param string $label         Label of the new webhook.
     * @param int $numConfirmations Number of confirmations before triggering the webhook.
     * @return array<string, mixed>
     */
    public function addBlockWebhook(string $url, string $type = 'block', ?string $label = null, ?int $numConfirmations = null)
    {
        return $this->addUserWebhook($url, self::blockWebhookType($type), $label, $numConfirmations);
    }

    /**
     * @deprecated 2.3.0 Same endpoint as listUserWebhooks(). Use listUserWebhooks().
     *
     * @return array<string, mixed>
     */
    public function listBlockWebhooks()
    {
        return $this->listUserWebhooks();
    }

    /**
     * @deprecated 2.3.0 Same endpoint as removeUserWebhook(). Use removeUserWebhook(), which sends $type unchanged.
     *
     * @param string $url   URL for callback requests to be made at.
     * @param string $type  'block' or 'wallet_confirmation'. Any other value is sent as 'block'.
     * @return array<string, mixed>
     */
    public function removeBlockWebhook(string $url, string $type = 'block')
    {
        return $this->removeUserWebhook($url, self::blockWebhookType($type));
    }

    /**
     * This API is still being developed. BitGo will update this section soon.
     *
     * @return array
     */
    public function getMarketPriceData()
    {
        $this->url = $this->APIEndpoint . '/market/latest';
        return $this->__execute('GET');
    }

    /**
     * Returns the recommended fee rate per kilobyte to confirm a transaction within a target number of blocks.
     *
     * @param int $numBlocks    The target number of blocks for the transaction to be confirmed. The accepted range is 1 - 1000 and the default value is 2.
     * @return array
     */
    public function estimateTransactionFees(?int $numBlocks = null)
    {
        $this->url = $this->APIEndpoint . '/tx/fee';
        $this->params = [
            'numBlocks' => $numBlocks
        ];
        return $this->__execute('GET');
    }

    private function __execute(string $requestType = 'POST')
    {
        $params = self::withoutNulls($this->params);
        $this->params = [];

        $ch = curl_init();
        curl_setopt_array($ch, $this->curlOptions($requestType, $params));

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        if ($errno !== 0) {
            return ['error' => $error !== '' ? $error : 'cURL error ' . $errno, 'status' => 0];
        }

        return self::decodeResponse((string) $response, $status);
    }

    /**
     * The cURL options for a request to $this->url.
     *
     * @param array<string, mixed> $params
     * @return array<int, mixed>
     */
    private function curlOptions(string $requestType, array $params): array
    {
        $options = [CURLOPT_URL => $this->url];
        if ($requestType === 'GET') {
            $options[CURLOPT_URL] = $this->url . '?' . self::buildQuery($params);
            $options[CURLOPT_CUSTOMREQUEST] = 'GET';
        } elseif ($requestType === 'PUT') {
            $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
            $options[CURLOPT_POSTFIELDS] = json_encode($params);
        } elseif ($requestType === 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            $options[CURLOPT_POSTFIELDS] = json_encode($params);
        } elseif ($requestType === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($params);
        }
        $options[CURLOPT_SSL_VERIFYPEER] = true;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_HTTPHEADER] = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken
        ];
        $options[CURLOPT_CAINFO] = dirname(__FILE__) . '/cacert.pem';
        if (isset($this->requestIP)) {
            $options[CURLOPT_INTERFACE] = $this->requestIP;
            // A bound local address can only reach hosts of the same IP version.
            $options[CURLOPT_IPRESOLVE] = filter_var($this->requestIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false ? CURL_IPRESOLVE_V4 : CURL_IPRESOLVE_V6;
        }
        if ($this->forceIPv4) {
            $options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }
        $options[CURLOPT_TIMEOUT] = $this->timeout;
        $options[CURLOPT_CONNECTTIMEOUT] = $this->connectTimeout;
        return $options;
    }

    /**
     * The type the deprecated *BlockWebhook() methods send: 'block' unless 'wallet_confirmation' was asked for.
     */
    private static function blockWebhookType(string $type): string
    {
        return in_array($type, ['block', 'wallet_confirmation'], true) ? $type : 'block';
    }

    /**
     * Whether this client's coin is one of $coins.
     *
     * @param list<string> $coins
     */
    private function coinIn(array $coins): bool
    {
        return in_array($this->coin, $coins, true);
    }

    /**
     * Decode a response body. A non-2xx status or an invalid JSON body becomes ['error' => …, 'status' => …],
     * keeping any other fields BitGo sent.
     *
     * @return mixed
     */
    private static function decodeResponse(string $body, int $status)
    {
        $decoded = json_decode($body, true);
        $jsonError = trim($body) === '' ? null : (json_last_error() === JSON_ERROR_NONE ? null : json_last_error_msg());
        $ok = $status >= 200 && $status < 300;

        if ($ok && $jsonError === null) {
            return $decoded;
        }

        $result = is_array($decoded) ? $decoded : [];
        if (!isset($result['error'])) {
            $result['error'] = $ok ? 'Invalid JSON response: ' . $jsonError : 'HTTP ' . $status;
        }
        $result['status'] = $status;
        return $result;
    }

    /**
     * Drop only unset (null) params, so 0, false and '' are still sent.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private static function withoutNulls(array $params): array
    {
        return array_filter($params, static function ($value): bool {
            return $value !== null;
        });
    }

    /**
     * Build a query string that sends booleans as "true"/"false" instead of http_build_query's 1/0.
     *
     * @param array<string, mixed> $params
     */
    private static function buildQuery(array $params): string
    {
        return http_build_query(array_map(static function ($value) {
            return is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }, $params));
    }
}
