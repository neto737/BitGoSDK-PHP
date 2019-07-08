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

class BitGoExpress implements IBitGoExpress {

    private $APIEndpoint = null;
    private $AuthAPIEndpoint = null;
    private $url = null;
    private $params = [];
    private $allowedCoins = ['btc', 'bch', 'bsv', 'btg', 'eth', 'dash', 'ltc', 'xrp', 'zec', 'rmg', 'erc', 'omg', 'zrx', 'fun', 'gnt', 'rep', 'bat', 'knc', 'cvc', 'eos', 'qrl', 'nmr', 'pay', 'brd', 'tbtc', 'tbch', 'teth', 'tdash', 'tltc', 'tzec', 'txrp', 'trmg', 'terc'];
    private $UTXObased = ['btc', 'bch', 'bsv', 'btg', 'dash', 'ltc', 'rmg', 'zec', 'tbtc', 'tbch', 'tbsv', 'tdash', 'tltc', 'tzec', 'trmg'];
    private $login = false;
    public $accessToken = null;
    public $walletId = null;

    /**
     * BitGoExpress Initialization
     * 
     * @param string $hostname  Set the hostname of your BitGo Express instance
     * @param int $port         Set the port of your BitGo Express instance
     * @param string $coin      Select the coin what you want to use with the BitGOSDK (use CurrencyCode class to select)
     * @throws \Exception
     */
    public function __construct(string $hostname, int $port, string $coin = CurrencyCode::BITCOIN) {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->coin = $coin;
        $this->AuthAPIEndpoint = 'http://' . $this->hostname . ':' . $this->port . '/api/v2';
        $this->APIEndpoint = 'http://' . $this->hostname . ':' . $this->port . '/api/v2/' . $this->coin;

        if (!in_array($this->coin, $this->allowedCoins)) {
            throw new \Exception('You are trying to use an invalid coin!');
        }
    }

    /**
     * Get a token for first-party access to the BitGo API. First-party access is only intended for users accessing their own BitGo accounts.
     * 
     * @param string $email     The user’s email address
     * @param string $password  The user’s password
     * @param string $otp       The 2nd-factor-authentication token
     * @param bool $extensible  True if the session is supposed to be extensible beyond a one-hour duration.
     * @return array
     */
    public function login(string $email, string $password, string $otp, bool $extensible = null) {
        $this->url = $this->AuthAPIEndpoint . '/user/login';
        $this->params = [
            'email' => $email,
            'password' => $password,
            'otp' => $otp,
            'extensible' => $extensible
        ];
        $this->login = true;
        return $this->__execute();
    }

    /**
     * Ping bitgo express to ensure that it is still running. Unlike /ping, this does not try connecting to bitgo.com.
     * 
     * @return array
     */
    public function ping() {
        $this->url = $this->AuthAPIEndpoint . '/ping';
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
    public function generateWallet(string $label, string $passphrase, string $userKey = null, string $backupXpub = null, string $backupXpubProvider = null, string $enterprise = null, bool $disableTransactionNotifications = null, int $gasPrice = null, string $passcodeEncryptionCode = null) {
        $this->url = $this->APIEndpoint . '/wallet/generate';
        $this->params = [
            'label' => $label,
            'passphrase' => $passphrase,
            'userKey' => $userKey,
            'backupXpub' => $backupXpub,
            'backupXpubProvider' => $backupXpubProvider,
            'enterprise' => $enterprise,
            'disableTransactionNotifications' => $disableTransactionNotifications,
            'gasPrice' => $this->coin === CurrencyCode::ETHEREUM ? $gasPrice : 0,
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
    public function addWallet(string $label, int $m, int $n, array $keys, string $enterprise = null, bool $isCold = null, bool $disableTransactionNotifications = null) {
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
     * @param int $amount                       Amount to be sent to the recipient
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
    public function sendTransaction(string $address, int $amount, string $walletPassphrase, string $prv = null, int $numBlocks = null, int $feeRate = null, string $comment = null, array $unspents = null, int $minConfirms = null, bool $enforceMinConfirmsForChange = null, int $targetWalletUnspents = null, bool $noSplitChange = null, int $minValue = null, int $maxValue = null, int $gasPrice = null, int $gasLimit = null, int $sequenceId = null, bool $segwit = null, int $lastLedgerSequence = null, string $ledgerSequenceDelta = null) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/sendcoins';
        $this->params = [
            'address' => $address,
            'amount' => "$amount",
            'walletPassphrase' => $walletPassphrase,
            'prv' => $prv,
            'numBlocks' => in_array($this->coin, $this->UTXObased) ? $numBlocks : null,
            'feeRate' => in_array($this->coin, $this->UTXObased) ? $feeRate : null,
            'comment' => $comment,
            'unspents' => in_array($this->coin, $this->UTXObased) ? $unspents : null,
            'minConfirms' => in_array($this->coin, $this->UTXObased) ? $minConfirms : null,
            'enforceMinConfirmsForChange' => in_array($this->coin, $this->UTXObased) ? $enforceMinConfirmsForChange : null,
            'targetWalletUnspents' => in_array($this->coin, $this->UTXObased) ? $targetWalletUnspents : null,
            'noSplitChange' => in_array($this->coin, $this->UTXObased) ? $noSplitChange : null,
            'minValue' => in_array($this->coin, $this->UTXObased) ? $minValue : null,
            'maxValue' => in_array($this->coin, $this->UTXObased) ? $maxValue : null,
            'gasPrice' => $this->coin === CurrencyCode::ETHEREUM ? $gasPrice : null,
            'gasLimit' => $this->coin === CurrencyCode::ETHEREUM ? $gasLimit : null,
            'sequenceId' => $this->coin === CurrencyCode::ETHEREUM ? $sequenceId : null,
            'segwit' => in_array($this->coin, [CurrencyCode::BITCOIN, CurrencyCode::LITECOIN, CurrencyCode::BITCOIN_GOLD]) ? $segwit : null,
            'lastLedgerSequence' => $this->coin === CurrencyCode::RIPPLE ? $lastLedgerSequence : null,
            'ledgerSequenceDelta' => $this->coin === CurrencyCode::RIPPLE ? $ledgerSequenceDelta : null
        ];
        return $this->__execute();
    }

    /**
     * This API call allows you to create a transaction and send to multiple addresses. 
     * This may be useful if you schedule outgoing transactions in bulk, as you will be able to process multiple recipients and lower the aggregate amount of blockchain fees paid.
     * 
     * @param array $recipients                 Array of recipient objects and the amount to send to each e.g. [{address: ‘38BKDNZbPcLogvVbcx2ekJ9E6Vv94DqDqw’, amount: 1500}, …]
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
    public function sendTransactionToMany(array $recipients, string $walletPassphrase, string $prv = null, int $numBlocks = null, int $feeRate = null, string $comment = null, array $unspents = null, int $minConfirms = null, bool $enforceMinConfirmsForChange = null, int $targetWalletUnspents = null, bool $noSplitChange = null, int $minValue = null, int $maxValue = null, int $gasPrice = null, int $gasLimit = null, int $sequenceId = null, bool $segwit = null, int $lastLedgerSequence = null, string $ledgerSequenceDelta = null) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/sendmany';
        $this->params = [
            'recipients' => $recipients,
            'walletPassphrase' => $walletPassphrase,
            'prv' => $prv,
            'numBlocks' => in_array($this->coin, $this->UTXObased) ? $numBlocks : null,
            'feeRate' => in_array($this->coin, $this->UTXObased) ? $feeRate : null,
            'comment' => $comment,
            'unspents' => in_array($this->coin, $this->UTXObased) ? $unspents : null,
            'minConfirms' => in_array($this->coin, $this->UTXObased) ? $minConfirms : null,
            'enforceMinConfirmsForChange' => in_array($this->coin, $this->UTXObased) ? $enforceMinConfirmsForChange : null,
            'targetWalletUnspents' => in_array($this->coin, $this->UTXObased) ? $targetWalletUnspents : null,
            'noSplitChange' => in_array($this->coin, $this->UTXObased) ? $noSplitChange : null,
            'minValue' => in_array($this->coin, $this->UTXObased) ? $minValue : null,
            'maxValue' => in_array($this->coin, $this->UTXObased) ? $maxValue : null,
            'gasPrice' => $this->coin === CurrencyCode::ETHEREUM ? $gasPrice : null,
            'gasLimit' => $this->coin === CurrencyCode::ETHEREUM ? $gasLimit : null,
            'sequenceId' => $this->coin === CurrencyCode::ETHEREUM ? $sequenceId : null,
            'segwit' => in_array($this->coin, [CurrencyCode::BITCOIN, CurrencyCode::LITECOIN, CurrencyCode::BITCOIN_GOLD]) ? $segwit : null,
            'lastLedgerSequence' => $this->coin === CurrencyCode::RIPPLE ? $lastLedgerSequence : null,
            'ledgerSequenceDelta' => $this->coin === CurrencyCode::RIPPLE ? $ledgerSequenceDelta : null
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
    public function consolidateWalletUnspents(string $walletPassphrase, int $numUnspentsToMake = null, int $limit = null, int $minValue = null, int $maxValue = null, int $minHeight = null, int $feeRate = null, int $feeTxConfirmTarget = null, int $maxFeePercentage = null, int $minConfirms = null, bool $enforceMinConfirmsForChange = null) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/consolidateunspents';
        $this->params = [
            'walletPassphrase' => $walletPassphrase,
            'numUnspentsToMake' => $numUnspentsToMake,
            'limit' => $limit,
            'minValue' => $minValue,
            'maxValue' => $maxValue,
            'minHeight' => $minHeight,
            'feeRate' => $feeRate,
            'feeTxConfirmTarget' => $this->coin === CurrencyCode::BITCOIN ? $feeTxConfirmTarget : null,
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
     * @return array
     */
    public function funoutWalletUnspents(string $walletPassphrase, string $xprv = null, int $maxNumInputsToUse = null, int $numUnspentsToMake = null, int $minValue = null, int $maxValue = null, int $minHeight = null, int $maxFeePercentage = null, int $minConfirms = null, bool $enforceMinConfirmsForChange = null, int $feeRate = null, int $feeTxConfirmTarget = null) {
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
            'feeTxConfirmTarget' => $this->coin === CurrencyCode::BITCOIN ? $feeTxConfirmTarget : null
        ];
        return $this->__execute();
    }

    /**
     * his SDK call attempts to move all of the funds of the wallet into the address provided.
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
    public function sweep(string $address, string $walletPassphrase, string $xprv = null, string $otp = null, int $feeRate = null, int $feeTxConfirmTarget = null, int $gasPrice = null, int $sequenceId = null, int $lastLedgerSequence = null, string $ledgerSequenceDelta = null) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/sweep';
        $this->params = [
            'address' => $address,
            'walletPassphrase' => $walletPassphrase,
            'xprv' => $xprv,
            'otp' => $otp,
            'feeRate' => in_array($this->coin, $this->UTXObased) ? $feeRate : null,
            'feeTxConfirmTarget' => in_array($this->coin, $this->UTXObased) ? $feeTxConfirmTarget : null,
            'gasPrice' => $this->coin === CurrencyCode::ETHEREUM ? $gasPrice : null,
            'sequenceId' => $sequenceId,
            'lastLedgerSequence' => $this->coin === CurrencyCode::RIPPLE ? $lastLedgerSequence : null,
            'ledgerSequenceDelta' => $this->coin === CurrencyCode::RIPPLE ? $ledgerSequenceDelta : null
        ];
        return $this->__execute();
    }

    /**
     * Create a transaction with the specified parameters. This builds a transaction object, but does not sign or send it.
     * 
     * @param array $recipients                 List of recipients in array
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
    public function buildTransaction(array $recipients, int $numBlocks = null, int $feeRate = null, int $minConfirms = null, bool $enforceMinConfirmsForChange = null, array $unspents = null, int $targetWalletUnspents = null, bool $noSplitChange = null, int $minValue = null, int $maxValue = null, int $gasPrice = null, int $lastLedgerSequence = null, string $ledgerSequenceDelta = null) {
        $this->url = $this->APIEndpoint . '/wallet/' . $this->walletId . '/tx/build';
        $this->params = [
            'recipients' => $recipients,
            'numBlocks' => in_array($this->coin, $this->UTXObased) ? $numBlocks : null,
            'feeRate' => in_array($this->coin, $this->UTXObased) ? $feeRate : null,
            'minConfirms' => in_array($this->coin, $this->UTXObased) ? $minConfirms : null,
            'enforceMinConfirmsForChange' => in_array($this->coin, $this->UTXObased) ? $enforceMinConfirmsForChange : null,
            'unspents' => in_array($this->coin, $this->UTXObased) ? $unspents : null,
            'targetWalletUnspents' => in_array($this->coin, $this->UTXObased) ? $targetWalletUnspents : null,
            'noSplitChange' => in_array($this->coin, $this->UTXObased) ? $noSplitChange : null,
            'minValue' => in_array($this->coin, $this->UTXObased) ? $minValue : null,
            'maxValue' => in_array($this->coin, $this->UTXObased) ? $maxValue : null,
            'gasPrice' => $this->coin === CurrencyCode::ETHEREUM ? $gasPrice : null,
            'lastLedgerSequence' => $this->coin === CurrencyCode::RIPPLE ? $lastLedgerSequence : null,
            'ledgerSequenceDelta' => $this->coin === CurrencyCode::RIPPLE ? $ledgerSequenceDelta : null
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
    public function signBuildTransaction($txPrebuild, string $prv = null, string $coldDerivationSeed = null, $keychain = null, string $walletPassphrase = null) {
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
     * @param string $comment       Any additional comment to attach to the transaaction
     * @return array
     */
    public function sendBuildTransaction($halfSigned, string $otp, string $txHex = null, string $comment = null) {
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
    public function createKeychain() {
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
    public function shareWallet(string $email, string $permissions, string $walletPassphrase = null, bool $skipKeychain = null, bool $disableEmail = null) {
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
    public function acceptWalletShare(string $walletShareId, string $newWalletPassphrase = null, string $userPassword = null, string $overrideEncryptedPrv = null) {
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
    public function updatePendingApproval(string $pendingApprovalId, string $state, string $otp) {
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
    public function encrypt(string $input, string $password) {
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
    public function decrypt(string $input, string $password) {
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
    public function verifyAddress(string $address) {
        $this->url = $this->APIEndpoint . '/verifyaddress';
        $this->params = [
            'address' => $address
        ];
        return $this->__execute();
    }

    private function __execute(string $requestType = 'POST', bool $array = true) {
        $ch = curl_init($this->url);
        if ($requestType === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array_filter($this->params)));
        } elseif ($requestType === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array_filter($this->params)));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (isset($this->accessToken) && !$this->login) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->accessToken
            ]);
        }
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, $array);
    }

}
