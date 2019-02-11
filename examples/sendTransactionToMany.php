<?php

require 'vendor/autoload.php';

use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\Enum\CurrencyCode;

$hostname = 'localhost';
$port = 3080;
$coin = CurrencyCode::BITCOIN_TESTNET;

$bitgo = new BitGoSDK('YOUR_API_KEY_HERE', $coin, true);

/**
 * To send any transaction with BitGoExpress SDK you need to unlock your wallet
 * If you're not using testnet to send coins, you need to unlock your wallet with
 * your OTP password (2FA)
 */
$bitgo->unlockSession('0000000');

$bitgoExpress = new BitGoExpress($hostname, $port, $coin);
$bitgoExpress->accessToken = 'YOUR_API_KEY_HERE';
$bitgoExpress->walletId = 'YOUR_WALLET_ID_HERE';

/**
 * $sendTo array needs to be a multidimensional array
 * All amounts in this array needs to be integer (int) values
 * If you need convert bitcoin value into satoshi value just use toSatoshi function 
 */
$sendTo = [
    [
        'address' => 'ADDRESS_1',
        'amount' => BitGoSDK::toSatoshi(0.25)
    ],
    [
        'address' => 'ADDRESS_2',
        'amount' => 20000
    ],
    [
        'address' => 'ADDRESS_3',
        'amount' => BitGoSDK::toSatoshi(0.5)
    ],
    //...
];

$sendToMany = $bitgoExpress->sendTransactionToMany($sendTo, 'YOUR_WALLET_PASSPHRASE');
var_dump($sendToMany);
