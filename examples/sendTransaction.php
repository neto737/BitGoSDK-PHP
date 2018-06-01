<?php

require 'vendor/autoload.php';

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Enum\CurrencyCode;

$hostname = 'localhost';
$port = 3080;
$coin = CurrencyCode::BITCOIN_TESTNET;

$bitgo = new BitGoSDK('YOUR_API_KEY_HERE', $coin, true);

$bitgo->unlockSession('0000000');

$bitgoExpress = new BitGoExpress($hostname, $port, $coin);
$bitgoExpress->accessToken = 'YOUR_API_KEY_HERE';
$bitgoExpress->walletId = 'YOUR_WALLET_ID_HERE';

/**
 * Send the amount in satoshi
 */
$sendTransaction = $bitgoExpress->sendTransaction('DESTINATION_ADDRESS', 100000, 'YOUR_WALLET_PASSPHRASE');
var_dump($sendTransaction);