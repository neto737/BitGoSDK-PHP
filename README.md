<h1 align="center">BitGoSDK PHP</h1>

<p align="center">
  A PHP client for the <a href="https://www.bitgo.com">BitGo</a> API and BitGo Express.<br>
  Manage wallets, addresses, transfers, policies and webhooks, and send coins, from your PHP code.
</p>

<p align="center">
  <a href="https://packagist.org/packages/neto737/bitgosdk-php"><img src="https://img.shields.io/packagist/v/neto737/bitgosdk-php?label=packagist" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/neto737/bitgosdk-php"><img src="https://img.shields.io/packagist/dt/neto737/bitgosdk-php" alt="Total Downloads"></a>
  <img src="https://img.shields.io/badge/php-%E2%89%A5%207.4-777BB4?logo=php&logoColor=white" alt="PHP 7.4 or later">
  <a href="https://github.com/neto737/BitGoSDK-PHP/actions/workflows/ci.yml"><img src="https://github.com/neto737/BitGoSDK-PHP/actions/workflows/ci.yml/badge.svg?branch=main" alt="CI"></a>
  <a href="https://codecov.io/gh/neto737/BitGoSDK-PHP"><img src="https://codecov.io/gh/neto737/BitGoSDK-PHP/branch/main/graph/badge.svg" alt="Coverage"></a>
  <a href="LICENSE"><img src="https://img.shields.io/github/license/neto737/BitGoSDK-PHP" alt="License"></a>
</p>

---

## Features

- **Two clients:** `BitGoSDK` talks to the BitGo API for wallets, addresses, transfers, keychains, policies, sharing, approvals and webhooks. `BitGoExpress` talks to your own [BitGo Express](#bitgo-express) instance for anything that needs your keys, such as creating wallets and sending coins.
- **Any BitGo coin,** mainnet or testnet, with `CurrencyCode` constants for 95 coins and tokens and `AddressType` constants for every UTXO address type.
- **Exact amounts:** pass an `int` or a string of digits, and amounts are always sent as strings. `toBaseUnits()` and `fromBaseUnits()` convert at any number of decimals without floats, so 18-decimal ETH amounts don't overflow.
- **Errors you can check:** a failed call returns `['error' => …, 'status' => …]` instead of throwing, so an `isset($r['error'])` check is all you need.
- **TLS verification on by default,** against the system CA store, with a custom CA option for Express or a TLS-inspecting proxy.
- **Request timeouts,** opt-in IPv4 forcing and binding to a local IP.
- **Plain PHP arrays** as results. The only dependencies are `ext-curl` and `composer/ca-bundle`.
- Tested on PHP 7.4 to 8.5.

## Requirements

- PHP 7.4 or later, with the cURL extension
- A BitGo account and an [access token](https://developers.bitgo.com/docs/get-started-access-tokens)
- A [BitGo Express](#bitgo-express) instance, version 14.1.3 or later, for anything that needs your keys

## Installation

```sh
composer require neto737/bitgosdk-php
```

## Quick start

```php
use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Enum\CurrencyCode;

// Calls that don't need your keys go straight to BitGo. The third argument selects testnet.
$bitgo = new BitGoSDK('YOUR_ACCESS_TOKEN', CurrencyCode::BITCOIN_TESTNET, true);
$bitgo->walletId = 'YOUR_WALLET_ID';

$wallet = $bitgo->getWallet();              // ['id' => '...', 'label' => '...', 'balanceString' => '...', ...]
$address = $bitgo->createWalletAddress();   // ['address' => '2N...', 'chain' => 10, ...]

// Sending needs an unlocked session. The OTP is always 000000 on testnet.
$unlock = $bitgo->unlockSession('000000');
if (isset($unlock['error'])) {
    exit($unlock['error']);
}

// Anything that signs goes through your BitGo Express instance
$express = new BitGoExpress('localhost', 3080, CurrencyCode::BITCOIN_TESTNET);
$express->accessToken = 'YOUR_ACCESS_TOKEN';
$express->walletId = 'YOUR_WALLET_ID';

$amount = BitGoSDK::toSatoshi('0.001');     // 100000. Pass BTC amounts as strings to avoid float rounding.
$tx = $express->sendTransaction('DESTINATION_ADDRESS', $amount, 'YOUR_WALLET_PASSPHRASE');
```

More examples are in [`examples/`](examples/).

> [!NOTE]
> Requests to Express carry wallet passphrases and private keys. Use plain `http://` only when Express runs on the same host. Otherwise see [Use TLS unless Express is on the same host](#use-tls-unless-express-is-on-the-same-host).

## What's covered

### `BitGoSDK`: the BitGo API

| Area | Methods |
| --- | --- |
| User and session | `getCurrentUserProfile()`, `getSessionInfo()`, `lockSession()`, `unlockSession()`, `logout()` |
| Wallets | `listWallets()`, `getWallet()`, `getWalletByAddress()`, `getTotalBalances()`, `getMaximumSpendable()`, `freezeWallet()` |
| Addresses | `createWalletAddress()`, `getWalletAddress()`, `getWalletAddresses()`, `updateWalletAddress()` |
| Transfers and unspents | `listWalletTransfers()`, `getWalletTransfer()`, `getWalletTransferBySequenceId()`, `getWalletTransactions()`, `getWalletTransaction()`, `listWalletUnspents()` |
| Keychains | `listKeychains()`, `getKeychain()`, `createBitGoKeychain()`, `createBackupKeychain()`, `addKeychain()` |
| Policies | `setVelocityPolicyRule()`, `updateVelocityPolicyRule()`, `removePolicyRule()` |
| Wallet sharing | `listWalletShares()`, `getWalletShare()`, `resendWalletShareInvite()`, `cancelWalletShare()`, `removeWalletUser()` |
| Pending approvals | `listPendingApprovals()`, `getPendingApproval()` |
| Webhooks | Wallet and user webhooks: `list…`, `add…`, `remove…` and `simulate…Webhook()`. `getWebhookPayload()` reads an incoming webhook. |
| Fees and prices | `estimateTransactionFees()`, `getMarketPriceData()` |
| Amounts | `toSatoshi()`, `toBTC()`, `toBaseUnits()`, `fromBaseUnits()` |

### `BitGoExpress`: your BitGo Express instance

| Area | Methods |
| --- | --- |
| Status and login | `ping()`, `pingExpress()`, `login()` |
| Wallets | `generateWallet()`, `addWallet()`, `createKeychain()` |
| Sending | `sendTransaction()`, `sendTransactionToMany()` |
| Build, sign and send in steps | `buildTransaction()`, `signBuildTransaction()`, `sendBuildTransaction()` |
| Unspent management | `consolidateWalletUnspents()`, `fanoutWalletUnspents()`, `sweep()` |
| Sharing and approvals | `shareWallet()`, `acceptWalletShare()`, `updatePendingApproval()` |
| Utilities | `encrypt()`, `decrypt()`, `verifyAddress()` |

Each method's parameters are documented in its docblock. For what each endpoint does, see the [BitGo API reference](https://developers.bitgo.com/reference/overview) and the [Express API reference](https://developers.bitgo.com/reference/express-overview).

## Error handling

Calls don't throw on API or network errors. They return an array with an `error` message and the HTTP `status`, plus any other fields BitGo sent:

```php
$transfer = $bitgo->getWalletTransfer('TRANSFER_ID');

if (isset($transfer['error'])) {
    if ($transfer['status'] === 0) {
        // no response: timeout, DNS, TLS, ...
    } elseif ($transfer['status'] === 404) {
        // no such transfer
    } else {
        // any other non-2xx status, or an invalid JSON body
    }
}
```

`buildTransaction()` and `signBuildTransaction()` return the same fields as an object.

## BitGo Express

BitGo signs transactions on the client, so sending coins, creating wallets and other calls that use your keys go through `BitGoExpress`, which talks to your own BitGo Express instance. See [Install BitGo Express](https://developers.bitgo.com/docs/get-started-express-install) to run one.

### Use TLS unless Express is on the same host

Over plain `http://`, Express is only safe when it listens on `localhost`. Otherwise, run Express with a TLS certificate and prefix the hostname with `https://`:

```php
$express = new BitGoExpress('https://express.internal', 3080, CurrencyCode::BITCOIN);

// For a self-signed or private-CA certificate, trust that certificate or CA file.
$express->setCaFile('/path/to/express.crt');
```

`setCaFile()` throws an `InvalidArgumentException` if the file can't be read. Certificates are verified by default. `setVerifyTls(false)` turns verification off, so use it only for local testing.

### Minimum Express version

Use BitGo Express **14.1.3 or later**. Older versions fail on two changes BitGo made on its side:

- **UTXO withdrawals from cold wallets** must use PSBT in production. BitGo needs Express 14.1.3 or later for this ([changelog](https://developers.bitgo.com/changelog/20251007)).
- **Send-to-many on EVM assets** (`sendTransactionToMany()` on ETH and other EVM coins) uses a new batcher contract since May 22, 2025, so Express releases from before that date can't send it ([changelog](https://developers.bitgo.com/changelog/20250522)).

## Client settings

Both clients have these settings:

```php
$bitgo->setTimeout(30);          // request timeout in seconds. Default: 60 (BitGoSDK) or 310 (BitGoExpress)
$bitgo->setConnectTimeout(5);    // connect timeout in seconds. Default: 10
$bitgo->setForceIPv4(true);      // use IPv4 only. Default: whatever the system allows
$bitgo->setCaFile('/path/to/ca.pem');
```

`BitGoSDK::setRequestIP('203.0.113.10')` sends requests from that local IP. It returns `false` on testnet or for an invalid IP.

### TLS certificates

Both clients check certificates against your system's CA store. If PHP can't find one, they use the Mozilla CA bundle from [`composer/ca-bundle`](https://github.com/composer/ca-bundle), so keep your dependencies up to date. To trust a different CA, for example behind a TLS-inspecting proxy, call `setCaFile('/path/to/ca.pem')`.

## Documentation

- [BitGo developer portal](https://developers.bitgo.com/)
- [BitGo API reference](https://developers.bitgo.com/reference/overview) and [Express API reference](https://developers.bitgo.com/reference/express-overview)
- [Webhooks](https://developers.bitgo.com/docs/webhooks-overview) and [policies](https://developers.bitgo.com/docs/policies-overview)

**Upgrading from 2.2.x?** 2.3 is a drop-in upgrade, but two behaviours changed: `0` and `false` params are now sent, and errors come back as `['error' => …, 'status' => …]`. Read the [Changed section of CHANGELOG.md](CHANGELOG.md#changed) before you upgrade.

## Contributing

Bug reports and pull requests are welcome. See [CONTRIBUTING.md](CONTRIBUTING.md) for the branch model and release process. To run the checks:

```sh
composer install
composer test       # PHPUnit
composer analyse    # PHPStan
composer cs         # PHP-CS-Fixer, without changing files
```

`composer cs:fix` fixes the coding style. The code follows PSR-12, with 4-space indentation and LF line endings.

The tests replace the HTTP transport with a recorder, so they don't need a BitGo account or network access.

To report a security issue, follow [SECURITY.md](SECURITY.md) instead of opening a public issue.

## Credits

Created and maintained by [Neto Melo](https://github.com/neto737).

This is a community project. It is not affiliated with or endorsed by BitGo, Inc.

## Support the project :heart:

If this library saves you time, you can support its development via [Liberapay](https://liberapay.com/neto737), [PayPal](https://www.paypal.com/donate/?business=T7RVRCXLZXB58&no_recurring=0&currency_code=USD) or crypto:

```
BTC: bc1pduj90df9cs3md3gym3q809slfv2x5phnpv8xznajys5q3tlulnzqt3flwn
ETH: 0xeef9220639F14E7A0FD825AAAd0574e5a8aD7A4B
LTC: ltc1q508qfkd09vyya6c5zkfx4r248pf3ezj9ngjdr2
```

## License

Released under the [MIT License](LICENSE). Versions 2.2.3 and earlier were released under GPL-3.0.
