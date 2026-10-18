<?php

require __DIR__ . '/../vendor/autoload.php';

use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\Enum\CurrencyCode;

$hostname = 'localhost';
$port = 3080;
$coin = CurrencyCode::BITCOIN_TESTNET;

$bitgo = new BitGoSDK('YOUR_API_KEY_HERE', $coin, true);

/**
 * To send any transaction with BitGoExpress SDK you need to unlock your session
 * with your OTP code (2FA). On testnet the OTP code is always 000000.
 */
$unlock = $bitgo->unlockSession('000000');
if (isset($unlock['error'])) {
    exit('Unlock failed: ' . $unlock['error'] . PHP_EOL);
}

$bitgoExpress = new BitGoExpress($hostname, $port, $coin);
$bitgoExpress->accessToken = 'YOUR_API_KEY_HERE';
$bitgoExpress->walletId = 'YOUR_WALLET_ID_HERE';

/**
 * Send the amount in satoshi. Pass BTC values as strings to avoid float rounding.
 */
$amount = BitGoSDK::toSatoshi('0.25');

$sendTransaction = $bitgoExpress->sendTransaction('DESTINATION_ADDRESS', $amount, 'YOUR_WALLET_PASSPHRASE');
var_dump($sendTransaction);
