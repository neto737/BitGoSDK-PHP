
# BitGoSDK PHP

BitGoSDK written in PHP. This SDK contains methods for easily interacting with the BitGo API.

[![Latest Stable Version](https://poser.pugx.org/neto737/bitgosdk-php/version?style=for-the-badge)](https://packagist.org/packages/neto737/bitgosdk-php)
[![Total Downloads](https://poser.pugx.org/neto737/bitgosdk-php/downloads?style=for-the-badge)](https://packagist.org/packages/neto737/bitgosdk-php)
[![Latest Unstable Version](https://poser.pugx.org/neto737/bitgosdk-php/v/unstable?style=for-the-badge)](//packagist.org/packages/neto737/bitgosdk-php)
[![License](https://poser.pugx.org/neto737/bitgosdk-php/license?style=for-the-badge)](https://packagist.org/packages/neto737/bitgosdk-php)
[![PHP Version Require](https://poser.pugx.org/neto737/bitgosdk-php/require/php?style=for-the-badge)](https://packagist.org/packages/neto737/bitgosdk-php)

## Requirements

- PHP 7.0 or earlier with:
  - cURL
  - BCMath

## Installation

To install the SDK, you will need to be using [Composer](http://getcomposer.org/) in your project. If you aren't using Composer yet, it's really simple! Here's how to install composer and the BitGoSDK PHP.

```sh
# Install Composer
curl -sS https://getcomposer.org/installer | php

# Add the BitGoSDK as a dependency
php composer.phar require neto737/bitgosdk-php
```

Next, require Composer's autoloader, in your application, to automatically load the BitGoSDK in your project:

```php
require 'vendor/autoload.php';

use neto737\BitGoSDK\BitGoSDK;
```

Or if put the following in your `composer.json`:

```json
"neto737/bitgosdk-php": "*"
```
  
## Example

```php
require 'vendor/autoload.php';

use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Enum\CurrencyCode;

$bitgo = new BitGoSDK('YOUR_API_KEY_HERE', CurrencyCode::BITCOIN, false);
$bitgo->walletId = 'YOUR_WALLET_ID_HERE';

$createAddress = $bitgo->createWalletAddress();
```

## Attention

Keep your cacert.pem always up to date. You can find updates on the site [curl.haxx.se](https://curl.haxx.se/docs/caextract.html).

## Credits

- [Neto Melo](https://github.com/neto737)

## Donate

[![Donate BTC](https://img.shields.io/badge/donate-BTC-ff9900.svg?style=for-the-badge)](https://www.blockchain.com/btc/address/bc1pduj90df9cs3md3gym3q809slfv2x5phnpv8xznajys5q3tlulnzqt3flwn)
[![Donate ETH](https://img.shields.io/badge/donate-ETH-3C3C3D.svg?style=for-the-badge)](https://etherscan.io/address/0xeef9220639F14E7A0FD825AAAd0574e5a8aD7A4B)
[![Donate LTC](https://img.shields.io/badge/donate-LTC-D3D3D3.svg?style=for-the-badge)](https://blockchair.com/litecoin/address/ltc1q508qfkd09vyya6c5zkfx4r248pf3ezj9ngjdr2)
[![Donate with PayPal](https://img.shields.io/badge/donate-PayPal-blue.svg?style=for-the-badge)](https://www.paypal.com/donate/?business=T7RVRCXLZXB58&no_recurring=0&currency_code=USD)
