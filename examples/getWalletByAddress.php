<?php

require 'vendor/autoload.php';

use neto737\BitGoSDK\BitGoSDK;

$token = 'YOUR_API_KEY_HERE';
$coin = 'tbtc';

$bitgo = new BitGoSDK($token, $coin, true);

$getWalletByAddress = $bitgo->getWalletByAddress('WALLET_ADDRESS_HERE');
var_dump($getWalletByAddress);