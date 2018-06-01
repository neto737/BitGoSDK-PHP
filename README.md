
# BitGoSDK PHP

BitGoSDK written in PHP. This SDK contains methods for easily interacting with the BitGo API.

[![Latest Stable Version](https://poser.pugx.org/neto737/bitgosdk-php/version)](https://packagist.org/packages/neto737/bitgosdk-php) [![Total Downloads](https://poser.pugx.org/neto737/bitgosdk-php/downloads)](https://packagist.org/packages/neto737/bitgosdk-php) [![Latest Unstable Version](https://poser.pugx.org/neto737/bitgosdk-php/v/unstable)](//packagist.org/packages/neto737/bitgosdk-php) [![License](https://poser.pugx.org/neto737/bitgosdk-php/license)](https://packagist.org/packages/neto737/bitgosdk-php)

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
- <a href="https://github.com/neto737" target="_blank">Neto Melo</a>

## Donate
[![Donate BTC](https://img.shields.io/badge/donate-BTC-ff9900.svg)](https://blockchain.info/address/12oyGgGHYp1NxtoQFUmaoqm1z8XAeTQKUb) [![Donate ETH](https://img.shields.io/badge/donate-ETH-3C3C3D.svg)](https://etherscan.io/address/0xE461A5aC39a86Ec651AB49277637e6d4417257fA)
