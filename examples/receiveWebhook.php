<?php

require __DIR__ . '/../vendor/autoload.php';

use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Enum\CurrencyCode;

$coin = CurrencyCode::BITCOIN_TESTNET;

$bitgo = new BitGoSDK('YOUR_API_KEY_HERE', $coin, true);
$bitgo->walletId = 'WALLET_ID_HERE';

/**
 * Anyone who knows this URL can POST to it, so treat the payload as untrusted. Don't credit a deposit
 * because a webhook said so: look the transaction up on BitGo, as below, and act on what BitGo returns.
 * See the Webhooks guide on https://developers.bitgo.com/ for how BitGo signs webhooks.
 */
$payload = BitGoSDK::getWebhookPayload();

/**
 * Accept only a transaction hash: 64 hex digits, with a 0x prefix on EVM chains. Hashes on other chains
 * (Solana, Algorand, Hedera and others) look different, so adjust the pattern for your coin.
 */
if (!is_array($payload) || !isset($payload['hash']) || !is_string($payload['hash']) || preg_match('/^(0x)?[0-9a-fA-F]{64}$/', $payload['hash']) !== 1) {
    http_response_code(400);
    exit('Not a BitGo transfer webhook.');
}

/**
 * BitGo retries a webhook that isn't answered in time, so answer first and look the transaction up afterwards.
 * fastcgi_finish_request() is available under PHP-FPM. Elsewhere, queue the work instead.
 */
http_response_code(200);
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

$txDetails = $bitgo->getWalletTransaction($payload['hash']);

if (isset($txDetails['error'])) {
    error_log('BitGo webhook: ' . $txDetails['error']);
    exit;
}

if (!isset($txDetails['fromWallet'])) {
    //YOU ARE RECEIVING A TRANSACTION...
} else {
    //YOU ARE SENDING A TRANSACTION...
}
