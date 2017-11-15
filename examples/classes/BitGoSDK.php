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
 * @version 1.1
 */
class BitGoSDK {

    const BITGO_PRODUCTION_API_ENDPOINT = 'https://www.bitgo.com/api/v1';
    const BITGO_TESTNET_API_ENDPOINT = 'https://test.bitgo.com/api/v1';

    private $API_Endpoint = null;
    private $url = null;
    private $params = [];

    /**
     * @param string $accessToken Your BitGo API Key to get access
     * @param boolean $testNet    Enable or disable testnet API endpoint
     */
    public function __construct($accessToken, $testNet = false) {
        $this->accessToken = $accessToken;
        $this->testNet = $testNet;

        if ($this->testNet) {
            $this->API_Endpoint = self::BITGO_TESTNET_API_ENDPOINT;
        } else {
            $this->API_Endpoint = self::BITGO_PRODUCTION_API_ENDPOINT;
        }
    }

    /**
     * Gets details for a transaction hash
     * 
     * @param string $tx Bitcoin transaction hash
     * @return string    Decoded JSON as a array
     */
    public function getTransactionDetails(string $tx) {
        $this->url = $this->API_Endpoint . '/tx/' . $tx;
        return $this->execute('GET');
    }

    /**
     * Get the list of wallets for the user
     * 
     * @return string Decoded JSON as a array
     */
    public function listWallets() {
        $this->url = $this->API_Endpoint . '/wallet';
        return $this->execute('GET');
    }

    /**
     * Gets a list of addresses which have been instantiated for a wallet using the New Address API.
     * 
     * @param string $wallet Primary bitcoin address of your BitGo wallet
     * @return string        Decoded JSON as a array
     */
    public function listWalletAddresses(string $wallet) {
        $this->url = $this->API_Endpoint . '/wallet/' . $wallet . '/addresses';
        return $this->execute('GET');
    }

    /**
     * Get transactions for a given wallet, ordered by reverse block height (unconfirmed transactions first).
     * 
     * @param string $wallet Primary bitcoin address of your BitGo wallet
     * @return string        Decoded JSON as a array
     */
    public function listWalletTransactions(string $wallet) {
        $this->url = $this->API_Endpoint . '/wallet/' . $wallet . '/tx';
        return $this->execute('GET');
    }

    /**
     * Creates a new address for an existing wallet. BitGo wallets consist of two independent chains of 
     * addresses, designated 0 and 1. The 0-chain is typically used for receiving funds, while the 1-chain is used 
     * internally for creating change when spending from a wallet. It is considered best practice to generate a 
     * new receiving address for each new incoming transaction, in order to help maximize privacy.
     * 
     * @param string $wallet Primary bitcoin address of your BitGo wallet
     * @param int $chain     0-chain is recommended if you need to receive payments
     * @return string        Decoded JSON as a array
     */
    public function createAddress(string $wallet, int $chain) {
        $this->url = $this->API_Endpoint . '/wallet/' . $wallet . '/address/' . $chain;
        $this->params = [
            'wallet' => $wallet,
            'chain' => $chain
        ];
        return $this->execute();
    }

    /**
     * Lookup an address with balance info.
     * 
     * @param string $address Bitcoin address
     * @return string         Decoded JSON as a array
     */
    public function getAddressDetails(string $address) {
        $this->url = $this->API_Endpoint . '/address/' . $address;
        return $this->execute('GET');
    }

    /**
     * Get transactions for a given address, ordered by reverse block height.
     * 
     * @param string $address Bitcoin address
     * @return string         Decoded JSON as a array
     */
    public function getAddressTransactions(string $address) {
        $this->url = $this->API_Endpoint . '/address/' . $address . '/tx';
        return $this->execute('GET');
    }

    /**
     * Get the list of labels for the user
     * 
     * @return string Decoded JSON as a array
     */
    public function listAllWalletsLabels() {
        $this->url = $this->API_Endpoint . '/labels';
        return $this->execute('GET');
    }

    /**
     * Get the list of labels for the wallet
     * 
     * @param string $wallet Primary bitcoin address of your BitGo wallet
     * @return string        Decoded JSON as a array
     */
    public function listWalletLabels(string $wallet) {
        $this->url = $this->API_Endpoint . '/labels/' . $wallet;
        return $this->execute('GET');
    }

    /**
     * Set a label on a specific address and associate it with a specific wallet. Labels are limited to 250 
     * characters in length. Labels cannot be set on a wallet’s first receiving address because it reserved for the 
     * wallet’s label.
     * 
     * @param string $wallet  Primary bitcoin address of your BitGo wallet
     * @param string $address Bitcoin address which you want to change label
     * @param string $label   The label which you want
     * @return string         Decoded JSON as a array
     */
    public function setLabel(string $wallet, string $address, string $label) {
        $this->url = $this->API_Endpoint . '/labels/' . $wallet . '/' . $address;
        $this->params['label'] = $label;
        return $this->execute('PUT');
    }

    /**
     * Delete a label from a specific address and wallet.
     * 
     * @param string $wallet  Primary bitcoin address of your BitGo wallet
     * @param string $address Bitcoin address which you want to delete label
     * @return string         Decoded JSON as a array
     */
    public function deleteLabel(string $wallet, string $address) {
        $this->url = $this->API_Endpoint . '/labels/' . $wallet . '/' . $address;
        return $this->execute('DELETE');
    }

    /**
     * Get the list of public keychains for the user
     * 
     * @return string Decoded JSON as a array
     */
    public function listKeychain() {
        $this->url = $this->API_Endpoint . '/keychain';
        return $this->execute('GET');
    }

    /**
     * Lookup a keychain by xpub
     * 
     * @param string $xpub The BIP32 xpub to lookup
     * @return string      Decoded JSON as a array
     */
    public function getKeychain(string $xpub) {
        $this->url = $this->API_Endpoint . '/keychain/' . $xpub;
        $this->params['xpub'] = $xpub;
        return $this->execute();
    }

    /**
     * Creates a new keychain on BitGo’s servers and returns the public keychain to the caller.
     * 
     * @return string Decoded JSON as a array
     */
    public function createBitGoKeychain() {
        $this->url = $this->API_Endpoint . '/keychain/bitgo';
        return $this->execute();
    }

    private function execute(string $requestType = 'POST') {
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
