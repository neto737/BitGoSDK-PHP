<?php

require __DIR__ . '/../vendor/autoload.php';

use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Enum\CurrencyCode;

$coin = CurrencyCode::BITCOIN_TESTNET;

$bitgo = new BitGoSDK('YOUR_API_KEY_HERE', $coin, true);
$bitgo->walletId = 'WALLET_ID_HERE';

$createAddress = $bitgo->createWalletAddress();
var_dump($createAddress);
