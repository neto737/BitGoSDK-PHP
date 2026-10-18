# BitGoSDK PHP

BitGoSDK written in PHP. This SDK contains methods for easily interacting with the BitGo API.

[![Latest Stable Version](https://poser.pugx.org/neto737/bitgosdk-php/version?style=for-the-badge)](https://packagist.org/packages/neto737/bitgosdk-php)
[![Total Downloads](https://poser.pugx.org/neto737/bitgosdk-php/downloads?style=for-the-badge)](https://packagist.org/packages/neto737/bitgosdk-php)
[![Latest Unstable Version](https://poser.pugx.org/neto737/bitgosdk-php/v/unstable?style=for-the-badge)](//packagist.org/packages/neto737/bitgosdk-php)
[![License](https://poser.pugx.org/neto737/bitgosdk-php/license?style=for-the-badge)](https://packagist.org/packages/neto737/bitgosdk-php)
[![PHP Version Require](https://poser.pugx.org/neto737/bitgosdk-php/require/php?style=for-the-badge)](https://packagist.org/packages/neto737/bitgosdk-php)

## Requirements

- PHP 7.4 or newer, with the cURL extension
- A BitGo account and an [access token](https://developers.bitgo.com/docs/get-started-access-tokens)
- A [BitGo Express](#bitgo-express) instance, for anything that needs your keys, such as creating wallets or sending

## Installation

Install the SDK with [Composer](https://getcomposer.org/):

```sh
composer require neto737/bitgosdk-php
```

Or add it to the `require` section of your `composer.json`:

```json
"neto737/bitgosdk-php": "^2.3"
```

Then load Composer's autoloader in your application:

```php
require 'vendor/autoload.php';

use neto737\BitGoSDK\BitGoSDK;
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

More examples are in [`examples/`](examples/).

## BitGo Express

BitGo signs transactions on the client, so sending coins, creating wallets and other calls that use your keys go through `BitGoExpress`, which talks to your own BitGo Express instance. See [Install BitGo Express](https://developers.bitgo.com/docs/get-started-express-install) to run one, and the [Express API reference](https://developers.bitgo.com/reference/express-overview) for its endpoints.

## Documentation

- [BitGo developer portal](https://developers.bitgo.com/)
- [BitGo API reference](https://developers.bitgo.com/reference/overview)
- [Webhooks](https://developers.bitgo.com/docs/webhooks-overview) and [policies](https://developers.bitgo.com/docs/policies-overview)

## TLS certificates

Both clients check certificates against your system's CA store. If PHP can't find one, they use the Mozilla CA bundle from [`composer/ca-bundle`](https://github.com/composer/ca-bundle), so keep your dependencies up to date. To trust a different CA, for example behind a TLS-inspecting proxy, call `setCaFile('/path/to/ca.pem')`.

## License

MIT. See [LICENSE](LICENSE). Versions 2.2.3 and earlier were released under GPL-3.0.

## Credits

- [Neto Melo](https://github.com/neto737)

## Donate

[![Donate BTC](https://img.shields.io/badge/donate-BTC-ff9900.svg?style=for-the-badge)](https://www.blockchain.com/btc/address/bc1pduj90df9cs3md3gym3q809slfv2x5phnpv8xznajys5q3tlulnzqt3flwn)
[![Donate ETH](https://img.shields.io/badge/donate-ETH-3C3C3D.svg?style=for-the-badge)](https://etherscan.io/address/0xeef9220639F14E7A0FD825AAAd0574e5a8aD7A4B)
[![Donate LTC](https://img.shields.io/badge/donate-LTC-D3D3D3.svg?style=for-the-badge)](https://blockchair.com/litecoin/address/ltc1q508qfkd09vyya6c5zkfx4r248pf3ezj9ngjdr2)
[![Donate with PayPal](https://img.shields.io/badge/donate-PayPal-blue.svg?style=for-the-badge)](https://www.paypal.com/donate/?business=T7RVRCXLZXB58&no_recurring=0&currency_code=USD)
