<?php

require 'vendor/autoload.php';

use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Enum\CurrencyCode;

$coin = CurrencyCode::BITCOIN_TESTNET;

$bitgo = new BitGoSDK('YOUR_API_KEY_HERE', $coin, true);
$bitgo->walletId = 'WALLET_ID_HERE';

$payload = $bitgo->getWebhookPayload();

$txDetails = $bitgo->getWalletTransaction($payload['hash']);

if (isset($txDetails['error'])) {
    exit($txDetails['error']);
}

if (!isset($txDetails['fromWallet'])) {
    //YOU ARE RECEIVING A TRANSACTION...
} else {
    //YOU ARE SENDING A TRANSACTION...
}