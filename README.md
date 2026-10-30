<h1 align="center">BitGoSDK PHP</h1>

<p align="center">
  A PHP client for the <a href="https://www.bitgo.com">BitGo</a> API.<br>
  Manage wallets, addresses and webhooks from your PHP code, with exact amounts and typed errors.
</p>

<p align="center">
  <a href="https://packagist.org/packages/neto737/bitgosdk-php"><img src="https://img.shields.io/packagist/v/neto737/bitgosdk-php?include_prereleases&label=packagist" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/neto737/bitgosdk-php"><img src="https://img.shields.io/packagist/dt/neto737/bitgosdk-php" alt="Total Downloads"></a>
  <img src="https://img.shields.io/badge/php-%E2%89%A5%208.3-777BB4?logo=php&logoColor=white" alt="PHP 8.3 or later">
  <a href="https://github.com/neto737/BitGoSDK-PHP/actions/workflows/ci.yml"><img src="https://github.com/neto737/BitGoSDK-PHP/actions/workflows/ci.yml/badge.svg?branch=main" alt="CI"></a>
  <a href="https://codecov.io/gh/neto737/BitGoSDK-PHP"><img src="https://codecov.io/gh/neto737/BitGoSDK-PHP/branch/main/graph/badge.svg" alt="Coverage"></a>
  <a href="LICENSE"><img src="https://img.shields.io/github/license/neto737/BitGoSDK-PHP" alt="License"></a>
</p>

---

> [!WARNING]
> **This is 3.0, an alpha pre-release.** It covers addresses, wallets and webhooks, and its API may still change before 3.0.0. It can't send coins yet: BitGo Express, transfers and sending come in the 3.0 betas.
>
> **For production, use 2.3.x.** Its code and README are on the [`2.x` branch](https://github.com/neto737/BitGoSDK-PHP/tree/2.x#readme), and `composer require neto737/bitgosdk-php` still installs it.

## Features

- **Reads like BitGo's API:** `$bitgo->coin('tbtc')->wallet($id)->addresses()->create()`. Methods named after a thing only navigate. Methods named after an action send one request.
- **Any PSR-18 HTTP client,** such as Guzzle or Symfony HttpClient. Timeouts, proxies and TLS settings stay on your client.
- **Typed exceptions** for every failure, with the status, BitGo's error name, the request ID and the response.
- **Exact amounts:** `Amount` converts between `'0.5'` BTC and `'50000000'` satoshis with no floats, so 18-decimal amounts don't overflow.
- **Lazy pagination:** `list()` fetches the next batch as you iterate.
- **Opt-in retries** for reads and updates, with backoff and `Retry-After`. Requests that create or send are never retried.
- **Plain arrays** as results, with their main fields documented as array shapes for your IDE and PHPStan.
- Tested on PHP 8.3 to 8.5, with 100% line coverage.

## Requirements

- PHP 8.3 or later
- A PSR-18 HTTP client and PSR-17 factories, such as [Guzzle](https://docs.guzzlephp.org/) or [Symfony HttpClient](https://symfony.com/doc/current/http_client.html)
- A BitGo account and an [access token](https://developers.bitgo.com/docs/get-started-access-tokens)

## Installation

Install the alpha together with an HTTP client:

```sh
composer require neto737/bitgosdk-php:^3.0@alpha guzzlehttp/guzzle
```

If your project already has a PSR-18 client, leave `guzzlehttp/guzzle` out. The SDK finds the installed one with [`php-http/discovery`](https://github.com/php-http/discovery).

Composer may ask whether to trust the `php-http/discovery` plugin. If you allow it and your project has no PSR-18 client, it installs one for you and adds it to your `composer.json`. If you don't, install a client yourself as above.

## Quick start

```php
use neto737\BitGoSDK\BitGo;
use neto737\BitGoSDK\Config;

$bitgo = new BitGo(Config::testnet('YOUR_ACCESS_TOKEN'));   // Config::production() for mainnet
$wallet = $bitgo->coin('tbtc')->wallet('YOUR_WALLET_ID');   // sends no request

$data = $wallet->get();                                     // GET /api/v2/tbtc/wallet/{id}
echo $data['label'], ': ', $data['balanceString'] ?? '0', "\n";

$address = $wallet->addresses()->create(label: 'Deposit 42');
echo $address['address'], "\n";

foreach ($wallet->addresses()->list(labelContains: 'Deposit') as $address) {
    echo $address['address'], ' ', $address['label'] ?? '', "\n";
}
```

`wallet()` only holds the coin and the wallet ID, so `get()` fetches fresh data every time you call it.

## Setting a timeout

**The SDK doesn't set a timeout, and Guzzle's default is none**, so a stuck connection can block your process forever. Pass your own client with the timeouts you want:

```php
use GuzzleHttp\Client;

$bitgo = new BitGo(
    Config::production('YOUR_ACCESS_TOKEN'),
    new Client(['timeout' => 30, 'connect_timeout' => 10]),
);
```

Proxies, TLS settings and CA bundles go on that client too. The PSR-17 factories are still discovered. To pass your own, give them as the third and fourth arguments.

With Symfony HttpClient, pass `new Psr18Client(HttpClient::create(['timeout' => 30]))`.

## What's covered

| From | Methods |
| --- | --- |
| `$bitgo` | `coin($code)`, `lastResponse()` |
| `$bitgo->coin('tbtc')` | `wallet($id)`, `wallets()`, `blockWebhooks()` |
| `->wallets()` | `list()`, `findByAddress($address)` |
| `->wallet($id)` | `get()`, `addresses()`, `webhooks()` |
| `->addresses()` | `create()`, `get()`, `update()`, `list()`, `deploy()`, `forwardTokens()`, `getOwnershipProof()` |
| `->webhooks()` (wallet) | `add()`, `list()`, `remove()`, `simulate()` |
| `->blockWebhooks()` (coin) | `add()`, `list()`, `remove()`, `simulate()` |

Each method's parameters are documented in its docblock. For what each endpoint does, see the [BitGo API reference](https://developers.bitgo.com/reference/overview).

**Not in the alpha yet:** BitGo Express, sending coins, transfers, transactions and unspents, keychains, wallet creation, policies, sharing, pending approvals, and user and session calls. Use 2.3.x for these until the 3.0 betas add them.

## Usage

### Arguments

Every action takes named arguments. A `null` argument isn't sent, so BitGo's default applies. For a param the SDK doesn't name yet, use `extra`:

```php
$wallet->addresses()->create(chain: 10, label: 'Deposit 42', extra: ['someNewParam' => true]);
```

Named arguments win over the same key in `extra`.

### Coin codes

`coin()` takes any BitGo coin code as a string. `CoinCode` lists the major coins and their testnets, so your IDE can complete them:

```php
use neto737\BitGoSDK\CoinCode;

$bitgo->coin(CoinCode::TBTC);    // same as $bitgo->coin('tbtc')
$bitgo->coin(CoinCode::HTETH);   // Ethereum on Hoodi
$bitgo->coin('ttao');            // any code works as a string
```

### Pagination

`list()` returns a `Paginator`. It sends nothing until you iterate it, then fetches one batch per request:

```php
foreach ($bitgo->coin('tbtc')->wallets()->list(limit: 100) as $walletData) {
    echo $walletData['id'], ' ', $walletData['label'], "\n";
}

$all = iterator_to_array($wallet->addresses()->list());
```

`limit` is the batch size, not a total. Iterating again starts over and fetches everything again.

### Amounts

BitGo sends and expects amounts as strings of base units, such as satoshis or wei, and results keep them that way. `Amount` converts them without floats:

```php
use neto737\BitGoSDK\Amount;

Amount::fromDisplay('0.5', decimals: 8)->baseUnits;                     // '50000000'
Amount::fromBaseUnits($data['balanceString'] ?? '0', decimals: 8)->toDisplay(); // for example '1.25'
Amount::fromDisplay('10', CoinCode::ETH->decimals())->baseUnits;        // '10000000000000000000'
```

An `Amount` with more decimal places than the coin has throws instead of rounding. Anywhere a request takes an amount, an `Amount` is sent as its base units.

### Errors

Every failure throws an exception that implements `BitGoException`:

```php
use neto737\BitGoSDK\Exception\ApiException;
use neto737\BitGoSDK\Exception\AuthenticationException;
use neto737\BitGoSDK\Exception\NotFoundException;
use neto737\BitGoSDK\Exception\TransportException;

try {
    $data = $wallet->get();
} catch (NotFoundException) {
    // no such wallet
} catch (AuthenticationException $e) {
    // 401 or 403: a bad or expired token, or one without access to this wallet
} catch (ApiException $e) {
    // any other non-2xx status
    error_log($e->getStatusCode() . ' ' . $e->getErrorName() . ' ' . $e->getRequestId());
} catch (TransportException $e) {
    // no response: network error or timeout
}
```

| Exception | When |
| --- | --- |
| `AuthenticationException` | 401 or 403 |
| `NotFoundException` | 404 |
| `RateLimitException` | 429 |
| `ServerException` | 5xx |
| `ApiException` | any other non-2xx status (the parent of the four above) |
| `UnexpectedResponseException` | a 2xx whose body isn't a JSON object |
| `TransportException` | no response at all |
| `InvalidArgumentException` | a value rejected before sending, such as an empty wallet ID |

`ApiException::getRequestId()` is BitGo's ID for the failed request. Quote it when you contact BitGo support.

### Retries

Retries are off by default. To retry after a 429, a 5xx or a network error:

```php
use neto737\BitGoSDK\RetryPolicy;

$config = Config::production('YOUR_ACCESS_TOKEN')
    ->withRetryPolicy(new RetryPolicy(maxAttempts: 3, baseDelayMs: 500, maxDelayMs: 30_000));
```

Only `GET`, `PUT` and `DELETE` requests are retried, with exponential backoff and jitter, and `Retry-After` is followed. `POST` requests, such as creating an address, are never retried, because sending one twice could do the work twice.

### Response headers

Actions return the decoded body. For the status and headers of the latest request, use `lastResponse()` right after the call:

```php
$address = $wallet->addresses()->create(label: 'Deposit 42');
$date = $bitgo->lastResponse()?->getHeaderLine('Date');
```

### Receiving webhooks

Anyone who knows your webhook URL can POST to it, so treat the body as a hint. Look the transfer up on BitGo and act on what BitGo returns, not on the webhook body.

```php
$wallet->webhooks()->add(type: 'transfer', url: 'https://example.com/bitgo', numConfirmations: 1);
```

### Security

- `Config` accepts only `https://` URLs, since the access token is sent with every request.
- The access token is hidden from `var_dump()`, `print_r()` and stack traces.
- IDs and addresses are escaped into the request path, so they can't change which endpoint a request reaches. An empty ID, `.` or `..` throws before anything is sent.
- TLS certificates are verified by your HTTP client. Every mainstream client does this by default.

If requests fail with a `TransportException` such as `cURL error 60: unable to get local issuer certificate`, PHP has no CA bundle, which is common on Windows. Download [Mozilla's CA bundle](https://curl.se/docs/caextract.html) and set `curl.cainfo` and `openssl.cafile` in `php.ini` to its path, or pass it to your client, as in Guzzle's `['verify' => '/path/to/cacert.pem']`. Don't turn verification off.

## Upgrading from 2.x

3.0 is a new API, not a drop-in upgrade. 2.x's `BitGoSDK` and `BitGoExpress` classes are gone. An upgrade guide comes with the 3.0 betas, once Express and sending are in place. Until then, stay on 2.3.x for production. The [CHANGELOG](CHANGELOG.md) lists what has changed so far.

## Contributing

Bug reports and pull requests are welcome. See [CONTRIBUTING.md](CONTRIBUTING.md) for the branch model and release process, and [docs/adr/](docs/adr/) for the design decisions behind 3.0. To run the checks:

```sh
composer install
composer check    # coding standard, Rector, static analysis and tests
```

Each check can also run on its own: `composer cs`, `composer rector`, `composer analyse` and `composer test`. `composer cs:fix` and `composer rector:fix` apply their changes.

The tests run against a mock PSR-18 client, so they don't need a BitGo account or network access.

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
