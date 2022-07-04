
# BitGoSDK PHP

BitGoSDK written in PHP. This SDK contains methods for easily interacting with the BitGo API.

[![Latest Stable Version](https://poser.pugx.org/neto737/bitgosdk-php/version?style=for-the-badge)](https://packagist.org/packages/neto737/bitgosdk-php)
[![Total Downloads](https://poser.pugx.org/neto737/bitgosdk-php/downloads?style=for-the-badge)](https://packagist.org/packages/neto737/bitgosdk-php)
[![Latest Unstable Version](https://poser.pugx.org/neto737/bitgosdk-php/v/unstable?style=for-the-badge)](//packagist.org/packages/neto737/bitgosdk-php)
[![License](https://poser.pugx.org/neto737/bitgosdk-php/license?style=for-the-badge)](https://packagist.org/packages/neto737/bitgosdk-php)
[![PHP Version Require](https://poser.pugx.org/neto737/bitgosdk-php/require/php?style=for-the-badge)](https://packagist.org/packages/neto737/bitgosdk-php)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/neto737/BitGoSDK-PHP/PHP%20Composer?logo=github&style=for-the-badge)](https://github.com/neto737/BitGoSDK-PHP)
[![Codecov branch](https://img.shields.io/codecov/c/gh/neto737/BitGoSDK-PHP/v3?logo=codecov&style=for-the-badge&token=38KPL9BX5F)](https://app.codecov.io/gh/neto737/BitGoSDK-PHP)

## Requirements

- PHP 7.2 or newer with:

  - BCMath

## Installation

Install the library using Composer. Please read the [Composer Documentation](https://getcomposer.org/doc/01-basic-usage.md) if you are unfamiliar with Composer or dependency managers in general.

```sh
# Add the BitGoSDK as a dependency
composer require neto737/bitgosdk-php
```

Or if put the following in your `composer.json`:

```json
"require": {
    "neto737/bitgosdk-php": "~3.0"
}
```

## Example

```php
require 'vendor/autoload.php';

use neto737\BitGoSDK\Authentication\Authentication;
use neto737\BitGoSDK\Authentication\Environment;
use neto737\BitGoSDK\Enum\Environments;
use neto737\BitGoSDK\Client;

// Authenticate
$auth = new Authentication('YOUR_API_KEY_HERE');

// Create a new environment
$env = new Environment(Environments::TESTNET, 'YOUR_WALLET_ID_HERE', 'tbtc');

// Create a new Client instance
$client = new Client($auth, $env);

// Create a new Address instance
$address = $client->Address();

// Create a new wallet address
var_dump($address->create());
```

## Credits

- [Neto Melo](https://github.com/neto737)

## Donate

[![Donate BTC](https://img.shields.io/badge/donate-BTC-ff9900.svg?style=for-the-badge)](https://www.blockchain.com/btc/address/bc1pduj90df9cs3md3gym3q809slfv2x5phnpv8xznajys5q3tlulnzqt3flwn)
[![Donate ETH](https://img.shields.io/badge/donate-ETH-3C3C3D.svg?style=for-the-badge)](https://etherscan.io/address/0xeef9220639F14E7A0FD825AAAd0574e5a8aD7A4B)
[![Donate LTC](https://img.shields.io/badge/donate-LTC-D3D3D3.svg?style=for-the-badge)](https://blockchair.com/litecoin/address/ltc1q508qfkd09vyya6c5zkfx4r248pf3ezj9ngjdr2)
[![Donate with PayPal](https://img.shields.io/badge/donate-PayPal-blue.svg?style=for-the-badge)](https://www.paypal.com/donate/?business=T7RVRCXLZXB58&no_recurring=0&currency_code=USD)
