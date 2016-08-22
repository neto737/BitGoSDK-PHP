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

require_once 'classes/BitGoSDK.php';

$sdk = new BitGoSDK('YOUR_API_KEY_HERE', true);

//ADDRESS TO RECEIVE PAYMENTS (0-chain)
$createAddress = $sdk->createAddress('YOUR_PRIMARY_WALLET_ADDRESS_HERE', 0);

echo '<pre>';
echo 'Address: ' . $createAddress['address'] . '<br>';
echo 'Chain: ' . $createAddress['chain'] . '<br>';
echo 'Index: ' . $createAddress['index'] . '<br>';
echo 'Path: ' . $createAddress['path'] . '<br>';
echo 'Redeem Script: ' . $createAddress['redeemScript'] . '<br>';
echo 'Wallet: ' . $createAddress['wallet'] . '<br>';
echo '</pre>';
