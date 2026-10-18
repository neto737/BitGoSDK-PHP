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

use Composer\CaBundle\CaBundle;
use neto737\BitGoSDK\Enum\CurrencyCode;

class BitGoExpress
{
    // A little over BitGo Express's own 305 second request timeout, so Express reports its timeout first.
    const DEFAULT_TIMEOUT = 310;
    const DEFAULT_CONNECT_TIMEOUT = 10;

    // Coin families for coin-specific params. Each lists mainnet and testnet codes, so testnet wallets get the same params.
    private const UTXO_COINS = ['btc', 'tbtc', 'tbtc4', 'tbtcsig', 'bch', 'tbch', 'btg', 'tbtg', 'dash', 'tdash', 'doge', 'tdoge', 'ltc', 'tltc', 'zec', 'tzec'];
    private const BTC_COINS = ['btc', 'tbtc', 'tbtc4', 'tbtcsig'];
    private const SEGWIT_COINS = ['btc', 'tbtc', 'tbtc4', 'tbtcsig', 'btg', 'tbtg', 'ltc', 'tltc'];
    private const ETH_COINS = ['eth', 'teth', 'gteth', 'hteth', 'sepeth'];
    private const XRP_COINS = ['xrp', 'txrp'];

    private $APIEndpoint = null;
    private $AuthAPIEndpoint = null;
    private $url = null;
    private $params = [];
    public $accessToken = null;
    public $walletId = null;
    public string $hostname;
    public int $port;
    public string $coin;
    private int $timeout = self::DEFAULT_TIMEOUT;
    private int $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT;
    private ?string $caFile = null;
    private bool $verifyTls = true;
    private bool $forceIPv4 = false;

    /**
     * BitGoExpress Initialization
     *
     * @param string $hostname  Set the hostname of your BitGo Express instance. Prefix it with https:// if Express serves TLS.
     * @param int $port         Set the port of your BitGo Express instance
     * @param string $coin      Select the coin you want to use with the BitGoSDK (use CurrencyCode class to select)
     * @throws \InvalidArgumentException if the hostname has a scheme other than http:// or https://
     */
    public function __construct(string $hostname, int $port, string $coin = CurrencyCode::BITCOIN)
    {
        $scheme = 'http';
        if (preg_match('#^([a-z][a-z0-9+.-]*)://(.*)$#i', $hostname, $matches) === 1) {
            $scheme = strtolower($matches[1]);
            if ($scheme !== 'http' && $scheme !== 'https') {
                throw new \InvalidArgumentException('BitGo Express must be reached over http:// or https://.');
            }
            $hostname = rtrim($matches[2], '/');
        }

        $this->hostname = $hostname;
        $this->port = $port;
        $this->coin = $coin;
        $this->AuthAPIEndpoint = $scheme . '://' . $this->hostname . ':' . $this->port . '/api/v2';
        $this->APIEndpoint = $scheme . '://' . $this->hostname . ':' . $this->port . '/api/v2/' . $this->coin;
    }

    /**
     * Trust the CA certificates in a PEM file when connecting to an https:// Express.
     * For a self-signed Express certificate, pass the certificate file itself.
     *
     * @param string $path  Path to a PEM file
     * @throws \InvalidArgumentException if the file cannot be read
     */
    public function setCaFile(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \InvalidArgumentException('CA file not found or not readable: ' . $path);
        }
        $this->caFile = $path;
    }

    /**
     * Turn TLS certificate and hostname verification on or off for an https:// Express. It is on by default.
     * With it off, anyone who can intercept the connection can read passphrases and keys. Prefer setCaFile(),
     * and only turn verification off for local testing.
     *
     * @param bool $verify
     */
    public function setVerifyTls(bool $verify): void
    {
        $this->verifyTls = $verify;
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
     * Set the maximum time to wait for a connection to BitGo Express
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
     * Get a token for first-party access to the BitGo API. First-party access is only intended for users accessing their own BitGo accounts.
     *
     * @param string $email     The user’s email address
     * @param string $password  The user’s password
     * @param string $otp       The 2nd-factor-authentication token
     * @param bool $extensible  True if the session is supposed to be extensible beyond a one-hour duration.
     * @return array            On success, the returned access_token is also stored in $accessToken for later calls.
     */
    public function login(string $email, string $password, string $otp, ?bool $extensible = null)
    {
        $this->url = $this->AuthAPIEndpoint . '/user/login';
        $this->params = [
            'email' => $email,
            'password' => $password,
            'otp' => $otp,
            'extensible' => $extensible
        ];
        $result = $this->__execute('POST', true, false);
        $this->rememberAccessToken($result);
        return $result;
    }

    /**
     * Ping BitGo through BitGo Express, to check that Express is running and can reach BitGo.
     * Use pingExpress() to check Express alone.
     *
     * @return array
     */
    public function ping()
    {
        $this->url = $this->AuthAPIEndpoint . '/ping';
        return $this->__execute('GET');
    }

    /**
     * Ping BitGo Express to ensure that it is still running. Unlike ping(), this does not try connecting to bitgo.com.
     *
     * @return array<string, mixed>
     */
    public function pingExpress()
    {
        $this->url = $this->AuthAPIEndpoint . '/pingexpress';
        return $this->__execute('GET');
    }

    /**
     * This API call creates a new wallet.
     *
     * @param string $label                         Human-readable name for the wallet.
     * @param string $passphrase                    Passphrase to decrypt the wallet’s private key.
     * @param string $userKey                       Optional xpub to be used as the user key.
     * @param string $backupXpub                    Optional xpub to be used as the backup key.
     * @param string $backupXpubProvider            Optional key recovery service to provide and store the backup key.
     * @param string $enterprise                    ID of the enterprise to associate this wallet with.
     * @param bool $disableTransactionNotifications Will prevent wallet transaction notifications if set to true.
     * @param int $gasPrice                         Custom gas price to be used for the deployment of the wallet (only for Ethereum)
     * @param string $passcodeEncryptionCode        Encryption code for wallet passphrase (used for lost passphrase recovery)
     * @return array
     */
    public function generateWallet(string $label, string $passphrase, ?string $userKey = null, ?string $backupXpub = null, ?string $backupXpubProvider = null, ?string $enterprise = null, ?bool $disableTransactionNotifications = null, ?int $gasPrice = null, ?string $passcodeEncryptionCode = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/generate';
        $this->params = [
            'label' => $label,
            'passphrase' => $passphrase,
            'userKey' => $userKey,
            'backupXpub' => $backupXpub,
            'backupXpubProvider' => $backupXpubProvider,
            'enterprise' => $enterprise,
            'disableTransactionNotifications' => $disableTransactionNotifications,
            'gasPrice' => $this->coinIn(self::ETH_COINS) ? $gasPrice : null,
            'passcodeEncryptionCode' => $passcodeEncryptionCode
        ];
        return $this->__execute();
    }

    /**
     * This API creates a new wallet for the user.
     * The keys to use with the new wallet (passed in the ‘keys’ parameter) must be registered with BitGo prior to using this API.
     *
     * @param string $label                         Human-readable name for the wallet.
     * @param int $m                                Number of signatures required for wallet (must be 2).
     * @param int $n                                Number of total signers on wallet (must be 3).
     * @param array $keys                           Array of keychain IDs previously created using Keychain API. There must be three IDs in the following order: user key, backup key, and BitGo key.
     * @param string $enterprise                    ID of the enterprise to associate this wallet with.
     * @param bool $isCold                          Whether the wallet is a cold wallet (BitGo only knows public portion of user key).
     * @param bool $disableTransactionNotifications Will prevent wallet transaction notifications if set to true.
     * @return array
     */
    public function addWallet(string $label, int $m, int $n, array $keys, ?string $enterprise = null, ?bool $isCold = null, ?bool $disableTransactionNotifications = null)
    {
        $this->url = $this->APIEndpoint . '/wallet';
        $this->params = [
            'label' => $label,
            'm' => $m,
            'n' => $n,
            'keys' => $keys,
            'enterprise' => $enterprise,
            'isCold' => $isCold,
            'disableTransactionNotifications' => $disableTransactionNotifications
        ];
        return $this->__execute();
    }

    /**
     * This API call allows you to create and send cryptocurrency to a destination address.
     *
     * @param string $address                   Recipient address
     * @param int|string $amount                Amount to be sent to the recipient, in base units (e.g. satoshi or wei). Use a string for amounts above PHP_INT_MAX.
     * @param string $walletPassphrase          The passphrase to be used to decrypt the user key on this wallet
     * @param string $prv                       The private key in string form if the walletPassphrase is not available
     * @param int $numBlocks                    Estimates the approximate fee per kilobyte necessary for a transaction confirmation within 'numBlocks' blocks.
     * @param int $feeRate                      Fee rate in satoshis/litoshis/atoms per kilobyte.
     * @param string $comment                   Any additional comment to attach to the transaction
     * @param array $unspents                   The unspents to use in the transaction. Each unspent should be in the form prevTxId:nOutput.
     * @param int $minConfirms                  Minimum number of confirmations unspents going into this transaction should have.
     * @param bool $enforceMinConfirmsForChange Enforce minimum number of confirmations on change (internal) inputs.
     * @param int $targetWalletUnspents         The desired count of unspents in the wallet. If the wallet’s current unspent count is lower than the target, up to four additional change outputs will be added to the transaction. To reduce unspent count in your wallet see 'Consolidate Unspents’.
     * @param bool $noSplitChange               Set to true to disable automatic change splitting for purposes of unspent management.
     * @param int $minValue                     Ignore unspents smaller than this amount of satoshis
     * @param int $maxValue                     Ignore unspents larger than this amount of satoshis
     * @param int $gasPrice                     Custom gas price to be used for sending the transaction
     * @param int $gasLimit                     Custom gas limit for the transaction
     * @param int $sequenceId                   The sequence ID of the transaction
     * @param bool $segwit                      Allow SegWit unspents to be used, and create SegWit change.
     * @param int $lastLedgerSequence           Absolute max ledger the transaction should be accepted in, whereafter it will be rejected.
     * @param string $ledgerSequenceDelta       Relative ledger height (in relation to the current ledger) that the transaction should be accepted in, whereafter it will be rejected.
     * @return array
     */
    public function sendTransaction(string $address, $amount, string $walletPassphrase, ?string $prv = null, ?int $numBlocks = null, ?int $feeRate = null, ?string $comment = null, ?array $unspents = null, ?int $minConfirms = null, ?bool $enforceMinConfirmsForChange = null, ?int $targetWalletUnspents = null, ?bool $noSplitChange = null, ?int $minValue = null, ?int $maxValue = null, ?int $gasPrice = null, ?int $gasLimit = null, ?int $sequenceId = null, ?bool $segwit = null, ?int $lastLedgerSequence = null, ?string $ledgerSequenceDelta = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/sendcoins';
        $this->params = [
            'address' => $address,
            'amount' => self::amountToString($amount),
            'walletPassphrase' => $walletPassphrase,
            'prv' => $prv,
            'numBlocks' => $this->coinIn(self::UTXO_COINS) ? $numBlocks : null,
            'feeRate' => $this->coinIn(self::UTXO_COINS) ? $feeRate : null,
            'comment' => $comment,
            'unspents' => $this->coinIn(self::UTXO_COINS) ? $unspents : null,
            'minConfirms' => $this->coinIn(self::UTXO_COINS) ? $minConfirms : null,
            'enforceMinConfirmsForChange' => $this->coinIn(self::UTXO_COINS) ? $enforceMinConfirmsForChange : null,
            'targetWalletUnspents' => $this->coinIn(self::UTXO_COINS) ? $targetWalletUnspents : null,
            'noSplitChange' => $this->coinIn(self::UTXO_COINS) ? $noSplitChange : null,
            'minValue' => $this->coinIn(self::UTXO_COINS) ? $minValue : null,
            'maxValue' => $this->coinIn(self::UTXO_COINS) ? $maxValue : null,
            'gasPrice' => $this->coinIn(self::ETH_COINS) ? $gasPrice : null,
            'gasLimit' => $this->coinIn(self::ETH_COINS) ? $gasLimit : null,
            'sequenceId' => $this->coinIn(self::ETH_COINS) ? $sequenceId : null,
            'segwit' => $this->coinIn(self::SEGWIT_COINS) ? $segwit : null,
            'lastLedgerSequence' => $this->coinIn(self::XRP_COINS) ? $lastLedgerSequence : null,
            'ledgerSequenceDelta' => $this->coinIn(self::XRP_COINS) ? $ledgerSequenceDelta : null
        ];
        return $this->__execute();
    }

    /**
     * This API call allows you to create a transaction and send to multiple addresses.
     * This may be useful if you schedule outgoing transactions in bulk, as you will be able to process multiple recipients and lower the aggregate amount of blockchain fees paid.
     *
     * @param array $recipients                 Array of recipient objects and the amount to send to each e.g. [{address: ‘38BKDNZbPcLogvVbcx2ekJ9E6Vv94DqDqw’, amount: 1500}, …]. Amounts are int or string, in base units.
     * @param string $walletPassphrase          The passphrase to be used to decrypt the user key on this wallet
     * @param string $prv                       The private key in string form if the walletPassphrase is not available
     * @param int $numBlocks                    Estimates the approximate fee per kilobyte necessary for a transaction confirmation within 'numBlocks' blocks.
     * @param int $feeRate                      Fee rate in satoshis/litoshis/atoms per kilobyte.
     * @param string $comment                   Any additional comment to attach to the transaction
     * @param array $unspents                   The unspents to use in the transaction. Each unspent should be in the form prevTxId:nOutput.
     * @param int $minConfirms                  Minimum number of confirmations unspents going into this transaction should have.
     * @param bool $enforceMinConfirmsForChange Enforce minimum number of confirmations on change (internal) inputs.
     * @param int $targetWalletUnspents         The desired count of unspents in the wallet. If the wallet’s current unspent count is lower than the target, up to four additional change outputs will be added to the transaction. To reduce unspent count in your wallet see 'Consolidate Unspents’.
     * @param bool $noSplitChange               Set to true to disable automatic change splitting for purposes of unspent management.
     * @param int $minValue                     Ignore unspents smaller than this amount of satoshis
     * @param int $maxValue                     Ignore unspents larger than this amount of satoshis
     * @param int $gasPrice                     Custom gas price to be used for sending the transaction
     * @param int $gasLimit                     Custom gas limit for the transaction
     * @param int $sequenceId                   The sequence ID of the transaction
     * @param bool $segwit                      Allow SegWit unspents to be used, and create SegWit change.
     * @param int $lastLedgerSequence           Absolute max ledger the transaction should be accepted in, whereafter it will be rejected.
     * @param string $ledgerSequenceDelta       Relative ledger height (in relation to the current ledger) that the transaction should be accepted in, whereafter it will be rejected.
     * @return array
     */
    public function sendTransactionToMany(array $recipients, string $walletPassphrase, ?string $prv = null, ?int $numBlocks = null, ?int $feeRate = null, ?string $comment = null, ?array $unspents = null, ?int $minConfirms = null, ?bool $enforceMinConfirmsForChange = null, ?int $targetWalletUnspents = null, ?bool $noSplitChange = null, ?int $minValue = null, ?int $maxValue = null, ?int $gasPrice = null, ?int $gasLimit = null, ?int $sequenceId = null, ?bool $segwit = null, ?int $lastLedgerSequence = null, ?string $ledgerSequenceDelta = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/sendmany';
        $this->params = [
            'recipients' => self::recipientAmountsToStrings($recipients),
            'walletPassphrase' => $walletPassphrase,
            'prv' => $prv,
            'numBlocks' => $this->coinIn(self::UTXO_COINS) ? $numBlocks : null,
            'feeRate' => $this->coinIn(self::UTXO_COINS) ? $feeRate : null,
            'comment' => $comment,
            'unspents' => $this->coinIn(self::UTXO_COINS) ? $unspents : null,
            'minConfirms' => $this->coinIn(self::UTXO_COINS) ? $minConfirms : null,
            'enforceMinConfirmsForChange' => $this->coinIn(self::UTXO_COINS) ? $enforceMinConfirmsForChange : null,
            'targetWalletUnspents' => $this->coinIn(self::UTXO_COINS) ? $targetWalletUnspents : null,
            'noSplitChange' => $this->coinIn(self::UTXO_COINS) ? $noSplitChange : null,
            'minValue' => $this->coinIn(self::UTXO_COINS) ? $minValue : null,
            'maxValue' => $this->coinIn(self::UTXO_COINS) ? $maxValue : null,
            'gasPrice' => $this->coinIn(self::ETH_COINS) ? $gasPrice : null,
            'gasLimit' => $this->coinIn(self::ETH_COINS) ? $gasLimit : null,
            'sequenceId' => $this->coinIn(self::ETH_COINS) ? $sequenceId : null,
            'segwit' => $this->coinIn(self::SEGWIT_COINS) ? $segwit : null,
            'lastLedgerSequence' => $this->coinIn(self::XRP_COINS) ? $lastLedgerSequence : null,
            'ledgerSequenceDelta' => $this->coinIn(self::XRP_COINS) ? $ledgerSequenceDelta : null
        ];
        return $this->__execute();
    }

    /**
     * This SDK call will consolidate the unspents that match the parameters, and consolidate them into the number specified by 'numUnspentsToMake'.
     *
     * @param string $walletPassphrase          Passphrase to decrypt the wallet’s private key.
     * @param int $numUnspentsToMake            Number of outputs created by the consolidation transaction (Defaults to 1)
     * @param int $limit                        Number of unspents to select (Defaults to 25, Max is 200)
     * @param int $minValue                     Ignore unspents smaller than this amount of satoshis
     * @param int $maxValue                     Ignore unspents larger than this amount of satoshis
     * @param int $minHeight                    The minimum height of unspents on the block chain to use
     * @param int $feeRate                      The desired fee rate for the transaction in satoshis/kB
     * @param int $feeTxConfirmTarget           Fee rate is automatically chosen by targeting a transaction confirmation in this number of blocks (Only available on BTC, feeRate takes precedence if also set)
     * @param int $maxFeePercentage             Maximum percentage of an unspent’s value to be used for fees. Cannot be combined with minValue
     * @param int $minConfirms                  The required number of confirmations for each transaction input
     * @param bool $enforceMinConfirmsForChange Apply the required confirmations set in minConfirms for change outputs
     * @return array
     */
    public function consolidateWalletUnspents(string $walletPassphrase, ?int $numUnspentsToMake = null, ?int $limit = null, ?int $minValue = null, ?int $maxValue = null, ?int $minHeight = null, ?int $feeRate = null, ?int $feeTxConfirmTarget = null, ?int $maxFeePercentage = null, ?int $minConfirms = null, ?bool $enforceMinConfirmsForChange = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/consolidateunspents';
        $this->params = [
            'walletPassphrase' => $walletPassphrase,
            'numUnspentsToMake' => $numUnspentsToMake,
            'limit' => $limit,
            'minValue' => $minValue,
            'maxValue' => $maxValue,
            'minHeight' => $minHeight,
            'feeRate' => $feeRate,
            'feeTxConfirmTarget' => $this->coinIn(self::BTC_COINS) ? $feeTxConfirmTarget : null,
            'maxFeePercentage' => !isset($minValue) ? $maxFeePercentage : null,
            'minConfirms' => $minConfirms,
            'enforceMinConfirmsForChange' => $enforceMinConfirmsForChange
        ];
        return $this->__execute();
    }

    /**
     * This SDK call will fanout the unspents currently in the wallet that match the parameters, and use them as inputs to create more unspents.
     *
     * @param string $walletPassphrase          Passphrase to decrypt the wallet’s private key.
     * @param string $xprv                      The private key in string form if the walletPassphrase is not available
     * @param int $maxNumInputsToUse            Number of unspents you want to use in the fanout transaction (Default 20, Max 80)
     * @param int $numUnspentsToMake            Number of unspents you want to create in the transaction. They will all be almost the same size. (Default 200, Max 300)
     * @param int $minValue                     Ignore unspents smaller than this amount of satoshis
     * @param int $maxValue                     Ignore unspents larger than this amount of satoshis
     * @param int $minHeight                    The minimum height of unspents on the block chain to use
     * @param int $maxFeePercentage             Maximum percentage of an unspent’s value to be used for fees. Cannot be combined with minValue
     * @param int $minConfirms                  The required number of confirmations for each transaction input
     * @param bool $enforceMinConfirmsForChange Apply the required confirmations set in minConfirms for change outputs
     * @param int $feeRate                      The desired fee rate for the transaction in satoshis/kB
     * @param int $feeTxConfirmTarget           Fee rate is automatically chosen by targeting a transaction confirmation in this number of blocks (Only available on BTC, feeRate takes precedence if also set)
     * @return array<string, mixed>
     */
    public function fanoutWalletUnspents(string $walletPassphrase, ?string $xprv = null, ?int $maxNumInputsToUse = null, ?int $numUnspentsToMake = null, ?int $minValue = null, ?int $maxValue = null, ?int $minHeight = null, ?int $maxFeePercentage = null, ?int $minConfirms = null, ?bool $enforceMinConfirmsForChange = null, ?int $feeRate = null, ?int $feeTxConfirmTarget = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/fanoutunspents';
        $this->params = [
            'walletPassphrase' => $walletPassphrase,
            'xprv' => $xprv,
            'maxNumInputsToUse' => $maxNumInputsToUse,
            'numUnspentsToMake' => $numUnspentsToMake,
            'minValue' => $minValue,
            'maxValue' => $maxValue,
            'minHeight' => $minHeight,
            'maxFeePercentage' => !isset($minValue) ? $maxFeePercentage : null,
            'minConfirms' => $minConfirms,
            'enforceMinConfirmsForChange' => $enforceMinConfirmsForChange,
            'feeRate' => $feeRate,
            'feeTxConfirmTarget' => $this->coinIn(self::BTC_COINS) ? $feeTxConfirmTarget : null
        ];
        return $this->__execute();
    }

    /**
     * @deprecated 2.3.0 Misspelled. Use fanoutWalletUnspents(), which takes the same arguments.
     *
     * @return array<string, mixed>
     */
    public function funoutWalletUnspents(string $walletPassphrase, ?string $xprv = null, ?int $maxNumInputsToUse = null, ?int $numUnspentsToMake = null, ?int $minValue = null, ?int $maxValue = null, ?int $minHeight = null, ?int $maxFeePercentage = null, ?int $minConfirms = null, ?bool $enforceMinConfirmsForChange = null, ?int $feeRate = null, ?int $feeTxConfirmTarget = null)
    {
        return $this->fanoutWalletUnspents($walletPassphrase, $xprv, $maxNumInputsToUse, $numUnspentsToMake, $minValue, $maxValue, $minHeight, $maxFeePercentage, $minConfirms, $enforceMinConfirmsForChange, $feeRate, $feeTxConfirmTarget);
    }

    /**
     * This SDK call attempts to move all of the funds of the wallet into the address provided.
     *
     * @param string $address               The address to send all the funds in the wallet to.
     * @param string $walletPassphrase      Passphrase to decrypt the wallet’s private key.
     * @param string $xprv                  The private key in string form if the walletPassphrase is not available
     * @param string $otp                   The current 2FA code
     * @param int $feeRate                  Fee rate in satoshis/litoshis/atoms per kilobyte
     * @param int $feeTxConfirmTarget       Estimates the approximate fee per kilobyte necessary for a transaction confirmation within 'numBlocks' blocks.
     * @param int $gasPrice                 Custom gas price to be used for sending the transaction
     * @param int $sequenceId               The sequence ID of the transaction
     * @param int $lastLedgerSequence       Absolute max ledger the transaction should be accepted in, whereafter it will be rejected.
     * @param string $ledgerSequenceDelta   Relative ledger height (in relation to the current ledger) that the transaction should be accepted in, whereafter it will be rejected.
     * @return array
     */
    public function sweep(string $address, string $walletPassphrase, ?string $xprv = null, ?string $otp = null, ?int $feeRate = null, ?int $feeTxConfirmTarget = null, ?int $gasPrice = null, ?int $sequenceId = null, ?int $lastLedgerSequence = null, ?string $ledgerSequenceDelta = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/sweep';
        $this->params = [
            'address' => $address,
            'walletPassphrase' => $walletPassphrase,
            'xprv' => $xprv,
            'otp' => $otp,
            'feeRate' => $this->coinIn(self::UTXO_COINS) ? $feeRate : null,
            'feeTxConfirmTarget' => $this->coinIn(self::UTXO_COINS) ? $feeTxConfirmTarget : null,
            'gasPrice' => $this->coinIn(self::ETH_COINS) ? $gasPrice : null,
            'sequenceId' => $sequenceId,
            'lastLedgerSequence' => $this->coinIn(self::XRP_COINS) ? $lastLedgerSequence : null,
            'ledgerSequenceDelta' => $this->coinIn(self::XRP_COINS) ? $ledgerSequenceDelta : null
        ];
        return $this->__execute();
    }

    /**
     * Create a transaction with the specified parameters. This builds a transaction object, but does not sign or send it.
     *
     * @param array $recipients                 List of recipients in array. Amounts are int or string, in base units.
     * @param int $numBlocks                    Estimates the approximate fee per kilobyte necessary for a transaction confirmation within 'numBlocks' blocks.
     * @param int $feeRate                      Fee rate in satoshis/litoshis/atoms per kilobyte.
     * @param int $minConfirms                  Minimum number of confirmations unspents going into this transaction should have.
     * @param bool $enforceMinConfirmsForChange Enforce minimum number of confirmations on change (internal) inputs.
     * @param array $unspents                   The unspents to use in the transaction. Each unspent should be in the form prevTxId:nOutput.
     * @param int $targetWalletUnspents         The desired count of unspents in the wallet. If the wallet’s current unspent count is lower than the target, up to four additional change outputs will be added to the transaction. To reduce unspent count in your wallet see 'Consolidate Unspents’.
     * @param bool $noSplitChange               Set to true to disable automatic change splitting for purposes of unspent management.
     * @param int $minValue                     Ignore unspents smaller than this amount of satoshis
     * @param int $maxValue                     Ignore unspents larger than this amount of satoshis
     * @param int $gasPrice                     Custom gas price to be used for sending the transaction
     * @param int $lastLedgerSequence           Absolute max ledger the transaction should be accepted in, whereafter it will be rejected.
     * @param string $ledgerSequenceDelta       Relative ledger height (in relation to the current ledger) that the transaction should be accepted in, whereafter it will be rejected.
     * @return object                           Returns the newly created transaction description object.
     */
    public function buildTransaction(array $recipients, ?int $numBlocks = null, ?int $feeRate = null, ?int $minConfirms = null, ?bool $enforceMinConfirmsForChange = null, ?array $unspents = null, ?int $targetWalletUnspents = null, ?bool $noSplitChange = null, ?int $minValue = null, ?int $maxValue = null, ?int $gasPrice = null, ?int $lastLedgerSequence = null, ?string $ledgerSequenceDelta = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/tx/build';
        $this->params = [
            'recipients' => self::recipientAmountsToStrings($recipients),
            'numBlocks' => $this->coinIn(self::UTXO_COINS) ? $numBlocks : null,
            'feeRate' => $this->coinIn(self::UTXO_COINS) ? $feeRate : null,
            'minConfirms' => $this->coinIn(self::UTXO_COINS) ? $minConfirms : null,
            'enforceMinConfirmsForChange' => $this->coinIn(self::UTXO_COINS) ? $enforceMinConfirmsForChange : null,
            'unspents' => $this->coinIn(self::UTXO_COINS) ? $unspents : null,
            'targetWalletUnspents' => $this->coinIn(self::UTXO_COINS) ? $targetWalletUnspents : null,
            'noSplitChange' => $this->coinIn(self::UTXO_COINS) ? $noSplitChange : null,
            'minValue' => $this->coinIn(self::UTXO_COINS) ? $minValue : null,
            'maxValue' => $this->coinIn(self::UTXO_COINS) ? $maxValue : null,
            'gasPrice' => $this->coinIn(self::ETH_COINS) ? $gasPrice : null,
            'lastLedgerSequence' => $this->coinIn(self::XRP_COINS) ? $lastLedgerSequence : null,
            'ledgerSequenceDelta' => $this->coinIn(self::XRP_COINS) ? $ledgerSequenceDelta : null
        ];
        return $this->__execute('POST', false);
    }

    /**
     * Sign the given transaction with the specified keychain. All signing is done locally and can be performed offline.
     * Signing can happen two ways: with a prv argument representing the private key, or with keychain and walletPassphrase arguments (for signing with an encrypted private key).
     *
     * @param object $txPrebuild            The transaction description object, output from 'Build Transaction’
     * @param string $prv                   The user private key
     * @param string $coldDerivationSeed    The seed used to derive the signing key
     * @param object $keychain              The user keychain with an 'encryptedPrv' property
     * @param string $walletPassphrase      Passphrase to decrypt the user keychain
     * @return object                       The half-signed, serialized transaction hex
     */
    public function signBuildTransaction($txPrebuild, ?string $prv = null, ?string $coldDerivationSeed = null, $keychain = null, ?string $walletPassphrase = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/signtx';
        $this->params = [
            'txPrebuild' => $txPrebuild,
            'prv' => $prv,
            'coldDerivationSeed' => $coldDerivationSeed,
            'keychain' => $keychain,
            'walletPassphrase' => $walletPassphrase
        ];
        return $this->__execute('POST', false);
    }

    /**
     * Submit a half-signed transaction.
     *
     * @param object $halfSigned    The half-signed info returned from 'Sign Transaction’
     * @param string $otp           The current 2FA code
     * @param string $txHex         The half-signed, serialized transaction hex (alternative to halfSigned)
     * @param string $comment       Any additional comment to attach to the transaction
     * @return array
     */
    public function sendBuildTransaction($halfSigned, string $otp, ?string $txHex = null, ?string $comment = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/tx/send';
        $this->params = [
            'halfSigned' => $halfSigned,
            'otp' => $otp,
            'txHex' => $txHex,
            'comment' => $comment
        ];
        return $this->__execute();
    }

    /**
     * Local client-side function to create a new keychain.
     *
     * @return array
     */
    public function createKeychain()
    {
        $this->url = $this->APIEndpoint . '/keychain/local';
        return $this->__execute();
    }

    /**
     * Sharing a wallet involves giving another user permission to use the wallet through BitGo.
     *
     * @param string $email             Email of the user to share the wallet with
     * @param string $permissions       Comma-separated list of permissions, e.g. view,spend,admin
     * @param string $walletPassphrase  Passphrase on the wallet being shared
     * @param bool $skipKeychain        Set to true if sharing a wallet with another user who will obtain the keychain out-of-band
     * @param bool $disableEmail        Set to true to prevent a notification email sent to the user added
     * @return array
     */
    public function shareWallet(string $email, string $permissions, ?string $walletPassphrase = null, ?bool $skipKeychain = null, ?bool $disableEmail = null)
    {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/share';
        $this->params = [
            'email' => $email,
            'permissions' => $permissions,
            'walletPassphrase' => $walletPassphrase,
            'skipKeychain' => $skipKeychain,
            'disableEmail' => $disableEmail
        ];
        return $this->__execute();
    }

    /**
     * Client-side operation to accept a wallet share.
     *
     * @param string $walletShareId         The incoming wallet share ID to accept
     * @param string $newWalletPassphrase   The passphrase to set on the wallet, for use during future spends
     * @param string $userPassword          The user’s password to decrypt the shared private key
     * @param string $overrideEncryptedPrv  Set to an alternate encrypted prv if you wish to store an encrypted prv received out-of-band
     * @return array
     */
    public function acceptWalletShare(string $walletShareId, ?string $newWalletPassphrase = null, ?string $userPassword = null, ?string $overrideEncryptedPrv = null)
    {
        $this->url = $this->APIEndpoint . '/walletshare/' . $walletShareId . '/acceptshare';
        $this->params = [
            'walletShareId' => $walletShareId,
            'newWalletPassphrase' => $newWalletPassphrase,
            'userPassword' => $userPassword,
            'overrideEncryptedPrv' => $overrideEncryptedPrv
        ];
        return $this->__execute();
    }

    /**
     * Update the state of a pending approval to either ‘approved’ or 'rejected'. Pending approvals are designed to be managed through our web UI.
     *
     * @param string $pendingApprovalId Pending approval id.
     * @param string $state             Approval
     * @param string $otp               One Time Password
     * @return array
     */
    public function updatePendingApproval(string $pendingApprovalId, string $state, string $otp)
    {
        $this->url = $this->APIEndpoint . '/pendingapprovals/' . $pendingApprovalId;
        $this->params = [
            'state' => $state,
            'otp' => $otp
        ];
        return $this->__execute('PUT');
    }

    /**
     * Symmetrically encrypt an arbitrary message with provided password
     *
     * @param string $input     Plaintext message which should be encrypted
     * @param string $password  Password which should be used to encrypt message
     * @return string
     */
    public function encrypt(string $input, string $password)
    {
        $this->url = $this->AuthAPIEndpoint . '/encrypt';
        $this->params = [
            'input' => $input,
            'password' => $password
        ];
        return $this->__execute();
    }

    /**
     * Decrypt a ciphertext generated by encrypt route with provided password
     *
     * @param string $input     Ciphertext to decrypt
     * @param string $password  Key which is used for decryption
     * @return string
     */
    public function decrypt(string $input, string $password)
    {
        $this->url = $this->AuthAPIEndpoint . '/decrypt';
        $this->params = [
            'input' => $input,
            'password' => $password
        ];
        return $this->__execute();
    }

    /**
     * Verify address for a given coin
     *
     * @param string $address   Address which should be verified for correct format
     * @return array
     */
    public function verifyAddress(string $address)
    {
        $this->url = $this->APIEndpoint . '/verifyaddress';
        $this->params = [
            'address' => $address
        ];
        return $this->__execute();
    }

    private function __execute(string $requestType = 'POST', bool $array = true, bool $authenticate = true)
    {
        $params = self::withoutNulls($this->params);
        $this->params = [];

        $ch = curl_init();
        curl_setopt_array($ch, $this->curlOptions($requestType, $params, $authenticate));

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        if ($errno !== 0) {
            $result = ['error' => $error !== '' ? $error : 'cURL error ' . $errno, 'status' => 0];
            return $array ? $result : (object) $result;
        }

        return self::decodeResponse((string) $response, $status, $array);
    }

    /**
     * The cURL options for a request to $this->url.
     *
     * @param array<string, mixed> $params
     * @param bool $authenticate    False to leave out the Authorization header, as login() does
     * @return array<int, mixed>
     */
    private function curlOptions(string $requestType, array $params, bool $authenticate = true): array
    {
        $options = [CURLOPT_URL => $this->url];
        if ($requestType === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($params);
        } elseif ($requestType === 'PUT') {
            $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
            $options[CURLOPT_POSTFIELDS] = json_encode($params);
        }
        $options[CURLOPT_SSL_VERIFYPEER] = $this->verifyTls;
        $options[CURLOPT_SSL_VERIFYHOST] = $this->verifyTls ? 2 : 0;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $headers = ['Content-Type: application/json'];
        if ($authenticate && isset($this->accessToken)) {
            $headers[] = 'Authorization: Bearer ' . $this->accessToken;
        }
        $options[CURLOPT_HTTPHEADER] = $headers;
        // Without a custom CA file, use the system CA store, or composer/ca-bundle's Mozilla bundle if there is none.
        $caPath = $this->caFile ?? CaBundle::getSystemCaRootBundlePath();
        $options[is_dir($caPath) ? CURLOPT_CAPATH : CURLOPT_CAINFO] = $caPath;
        if ($this->forceIPv4) {
            $options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }
        $options[CURLOPT_TIMEOUT] = $this->timeout;
        $options[CURLOPT_CONNECTTIMEOUT] = $this->connectTimeout;
        return $options;
    }

    /**
     * Keep the token from a successful login, so later calls are authenticated with it.
     *
     * @param mixed $result
     */
    private function rememberAccessToken($result): void
    {
        if (is_array($result) && !isset($result['error']) && isset($result['access_token']) && is_string($result['access_token'])) {
            $this->accessToken = $result['access_token'];
        }
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
     * keeping any other fields Express sent. With $array false, the error comes back as an object.
     *
     * @return mixed
     */
    private static function decodeResponse(string $body, int $status, bool $array)
    {
        $decoded = json_decode($body, $array);
        $jsonError = trim($body) === '' ? null : (json_last_error() === JSON_ERROR_NONE ? null : json_last_error_msg());
        $ok = $status >= 200 && $status < 300;

        if ($ok && $jsonError === null) {
            return $decoded;
        }

        $result = is_array($decoded) ? $decoded : (is_object($decoded) ? get_object_vars($decoded) : []);
        if (!isset($result['error'])) {
            $result['error'] = $ok ? 'Invalid JSON response: ' . $jsonError : 'HTTP ' . $status;
        }
        $result['status'] = $status;
        return $array ? $result : (object) $result;
    }

    /**
     * Amounts can exceed PHP_INT_MAX (10 ETH is 1e19 wei), so they are always sent as strings.
     *
     * @param mixed $amount
     * @throws \InvalidArgumentException if the amount is not an integer
     */
    private static function amountToString($amount): string
    {
        return BitGoSDK::fromBaseUnits($amount, 0);
    }

    /**
     * @param array<mixed> $recipients
     * @return array<mixed>
     * @throws \InvalidArgumentException if an amount is not an integer
     */
    private static function recipientAmountsToStrings(array $recipients): array
    {
        return array_map(static function ($recipient) {
            if (is_array($recipient) && isset($recipient['amount'])) {
                $recipient['amount'] = self::amountToString($recipient['amount']);
            }
            return $recipient;
        }, $recipients);
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
}
