<?php

require 'vendor/autoload.php';

use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Enum\CurrencyCode;

$token = 'YOUR_API_KEY_HERE';
$coin = CurrencyCode::BITCOIN_TESTNET;

$bitgo = new BitGoSDK($token, $coin, true);

$getWalletByAddress = $bitgo->getWalletByAddress('WALLET_ADDRESS_HERE');
var_dump($getWalletByAddress);