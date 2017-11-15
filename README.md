# BitGoSDK PHP

BitGoSDK written in PHP.

## Requirements:
- PHP 7.0 or earlier with:
  - cURL

## Installation

BitGoSDK-PHP is available via [Composer/Packagist](https://packagist.org/packages/neto737/bitgosdk-php) (using semantic versioning), so just add this line to your `composer.json` file:

```json
"neto737/bitgosdk-php": "*"
```

or

```sh
composer require neto737/bitgosdk-php
```
  
## A simple example:

```php
require 'vendor/autoload.php';

$bitgo = new BitGoSDK('YOUR_API_KEY_HERE');
```

## Attention:

Keep your cacert.pem up to date. You can find updates on the site [curl.haxx.se](https://curl.haxx.se/docs/caextract.html).


## Credits:
- <a href="https://github.com/neto737" target="_blank">Neto Melo</a>
- Pedro Rodrigues

## Donations:
#### Donate: 12oyGgGHYp1NxtoQFUmaoqm1z8XAeTQKUb