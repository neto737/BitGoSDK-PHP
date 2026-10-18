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
 * $sendTo is a list of recipients, each with an address and an amount in base units (satoshi)
 * Amounts can be integers or strings of digits. Use toSatoshi() to convert a BTC value,
 * passing it as a string to avoid float rounding
 */
$sendTo = [
    [
        'address' => 'ADDRESS_1',
        'amount' => BitGoSDK::toSatoshi('0.25')
    ],
    [
        'address' => 'ADDRESS_2',
        'amount' => 20000
    ],
    [
        'address' => 'ADDRESS_3',
        'amount' => BitGoSDK::toSatoshi('0.5')
    ],
    //...
];

$sendToMany = $bitgoExpress->sendTransactionToMany($sendTo, 'YOUR_WALLET_PASSPHRASE');
var_dump($sendToMany);
