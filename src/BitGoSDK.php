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
 */

class BitGoSDK {

    const BITGO_PRODUCTION_API_ENDPOINT = 'https://bitgo.com/api/v1'; //PRODUCTION API ENDPOINT
    const BITGO_TESTNET_API_ENDPOINT = 'https://test.bitgo.com/api/v1'; //TESTNET API ENDPOINT
    private $API_Endpoint;
    
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
    public function getTransactionDetails($tx) {
        return json_decode(file_get_contents($this->API_Endpoint . '/tx/' . $tx), true);
    }

    /**
     * Get the list of wallets for the user
     * 
     * @return string Decoded JSON as a array
     */
    public function listWallets() {
        $curl = curl_init($this->API_Endpoint . '/wallet');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        $responseString = curl_exec($curl);
        curl_close($curl);

        return json_decode($responseString, true);
    }

    /**
     * Gets a list of addresses which have been instantiated for a wallet using the New Address API.
     * 
     * @param string $wallet Primary bitcoin address of your BitGo wallet
     * @return string        Decoded JSON as a array
     */
    public function listWalletAddresses($wallet) {
        $curl = curl_init($this->API_Endpoint . '/wallet/' . $wallet . '/addresses');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        $responseString = curl_exec($curl);
        curl_close($curl);

        return json_decode($responseString, true);
    }

    /**
     * Get transactions for a given wallet, ordered by reverse block height (unconfirmed transactions first).
     * 
     * @param string $wallet Primary bitcoin address of your BitGo wallet
     * @return string        Decoded JSON as a array
     */
    public function listWalletTransactions($wallet) {
        $curl = curl_init($this->API_Endpoint . '/wallet/' . $wallet . '/tx');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        $responseString = curl_exec($curl);
        curl_close($curl);

        return json_decode($responseString, true);
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
    public function createAddress($wallet, $chain) {
        $curl = curl_init($this->API_Endpoint . '/wallet/' . $wallet . '/address/' . $chain);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
                        [
                            'wallet' => $wallet,
                            'chain' => $chain
                        ]
        ));
        $responseString = curl_exec($curl);
        curl_close($curl);

        return json_decode($responseString, true);
    }

    /**
     * Lookup an address with balance info.
     * 
     * @param string $address Bitcoin address
     * @return string         Decoded JSON as a array
     */
    public function getAddressDetails($address) {
        return json_decode(file_get_contents($this->API_Endpoint . '/address/' . $address), true);
    }

    /**
     * Get transactions for a given address, ordered by reverse block height.
     * 
     * @param string $address Bitcoin address
     * @return string         Decoded JSON as a array
     */
    public function getAddressTransactions($address) {
        return json_decode(file_get_contents($this->API_Endpoint . '/address/' . $address . '/tx'), true);
    }

    /**
     * Get the list of labels for the user
     * 
     * @return string Decoded JSON as a array
     */
    public function listAllWalletsLabels() {
        $curl = curl_init($this->API_Endpoint . '/labels');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        $responseString = curl_exec($curl);
        curl_close($curl);

        return json_decode($responseString, true);
    }

    /**
     * Get the list of labels for the wallet
     * 
     * @param string $wallet Primary bitcoin address of your BitGo wallet
     * @return string        Decoded JSON as a array
     */
    public function listWalletLabels($wallet) {
        $curl = curl_init($this->API_Endpoint . '/labels/' . $wallet);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        $responseString = curl_exec($curl);
        curl_close($curl);

        return json_decode($responseString, true);
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
    public function setLabel($wallet, $address, $label) {
        $curl = curl_init($this->API_Endpoint . '/labels/' . $wallet . '/' . $address);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
            'label' => $label
        ]));
        $responseString = curl_exec($curl);
        curl_close($curl);

        return json_decode($responseString, true);
    }

    /**
     * Delete a label from a specific address and wallet.
     * 
     * @param string $wallet  Primary bitcoin address of your BitGo wallet
     * @param string $address Bitcoin address which you want to delete label
     * @return string         Decoded JSON as a array
     */
    public function deleteLabel($wallet, $address) {
        $curl = curl_init($this->API_Endpoint . '/labels/' . $wallet . '/' . $address);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        $responseString = curl_exec($curl);
        curl_close($curl);

        return json_decode($responseString, true);
    }

    /**
     * Get the list of public keychains for the user
     * 
     * @return string Decoded JSON as a array
     */
    public function listKeychain() {
        $curl = curl_init($this->API_Endpoint . '/keychain');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        $responseString = curl_exec($curl);
        curl_close($curl);

        return json_decode($responseString, true);
    }

    /**
     * Lookup a keychain by xpub
     * 
     * @param string $xpub The BIP32 xpub to lookup
     * @return string      Decoded JSON as a array
     */
    public function getKeychain($xpub) {
        $curl = curl_init($this->API_Endpoint . '/keychain/' . $xpub);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
                        [
                            'xpub' => $xpub
                        ]
        ));
        $responseString = curl_exec($curl);
        curl_close($curl);

        return json_decode($responseString, true);
    }

    /**
     * Creates a new keychain on BitGo’s servers and returns the public keychain to the caller.
     * 
     * @return string Decoded JSON as a array
     */
    public function createBitGoKeychain() {
        $curl = curl_init($this->API_Endpoint . '/keychain/bitgo');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        curl_setopt($curl, CURLOPT_POST, true);
        $responseString = curl_exec($curl);
        curl_close($curl);

        return json_decode($responseString, true);
    }

}
