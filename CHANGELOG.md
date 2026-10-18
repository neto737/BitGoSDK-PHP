# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Entries for 2.2.3 and earlier were reconstructed from the git history in October 2026, so they are high level. Release dates are the dates of the tagged commits.

## [Unreleased]

### Added

- `CHANGELOG.md`, `SECURITY.md` and a full `CONTRIBUTING.md` (branch model, local setup, coding style, tests and release process).
- `.editorconfig` and `.gitattributes`.

### Changed

- The project is now licensed under MIT. 2.2.3 and earlier were released under GPL-3.0.
- Composer dist archives no longer include `tests/`, `examples/`, `docs/` or dotfiles. The examples are still available in the repository.

## [2.2.3] - 2022-06-29

### Added

- Taproot addresses: `AddressType::BECH32M_DEPOSIT` (30) and `AddressType::BECH32M_CHANGE` (31).
- `CurrencyCode` constants for Algorand, Avalanche C-Chain, Casper, Celo, EOS testnet, Hedera, RSK Smart Bitcoin, Stacks, Tezos and Tron.

### Changed

- API endpoints moved to `app.bitgo.com` (production) and `app.bitgo-test.com` (testnet).
- Updated the bundled `cacert.pem`.

### Removed

- `CurrencyCode` constants for Bitcoin SV, Royal Mint Gold and the ERC testnet token.

## [2.2.2] - 2020-02-28

### Fixed

- `setRequestIP()` no longer has any effect in testnet mode.

## [2.2.1] - 2019-12-14

### Fixed

- `toBTC()` returns a string again. 2.2.0 had reintroduced the `float` cast that 2.1.7 removed.

## [2.2.0] - 2019-12-01

### Added

- `BitGoSDK::setRequestIP()` binds outgoing requests to a specific local IP address.
- `AddressType` enum (legacy, P2SH and bech32 chains) and support for creating bech32 addresses.
- `CurrencyCode` constants for Stellar.

### Changed

- **Breaking:** `createWalletAddress()` now takes `$chain`, `$label`, `$lowPriority` and `$gasPrice`. The `$allowMigrated` parameter was removed and the default chain changed from 0 to 10 (`AddressType::P2SH_DEPOSIT`).
- **Breaking:** `updateWalletAddress()` and `getWalletAddress()` gained `$dt` and `$memoId` parameters, and `$label` moved to the end of `updateWalletAddress()`.
- **Breaking:** `getWalletAddresses()` now takes `$labelContains`, `$limit`, `$mine`, `$prevId`, `$chains` and `$sort`.

### Removed

- **Breaking:** the `IBitGoSDK` and `IBitGoExpress` interfaces.

## [2.1.9] - 2019-09-02

### Fixed

- Requests are now forced over IPv4, which fixes access tokens restricted to an IPv4 address.

## [2.1.8] - 2019-07-08

### Added

- `BitGoExpress`: `ping()`, `encrypt()`, `decrypt()` and `verifyAddress()`.
- `BitGoSDK`: `listBlockWebhooks()`, `addBlockWebhook()` and `removeBlockWebhook()`.

### Changed

- Renamed the interfaces from `BitGoSDKInterface` and `BitGoExpressInterface` to `IBitGoSDK` and `IBitGoExpress`.

## [2.1.7] - 2019-04-18

### Fixed

- `toBTC()` returns the formatted string instead of casting it to `float`, which lost precision.

## [2.1.6] - 2019-04-13

### Added

- `CurrencyCode` constants for Bitcoin SV, Dash and Zcash (mainnet and testnet).

## [2.1.5] - 2019-02-11

### Changed

- `getWebhookPayload()` is now static.

### Fixed

- Session and user endpoints (`user/me`, `user/session`, `user/lock`, `user/unlock`, `user/logout`) no longer build URLs with a double slash.

## [2.1.4] - 2019-01-20

### Changed

- Optional parameters of `login()`, `generateWallet()`, `addWallet()` and `consolidateWalletUnspents()` default to `null`, so BitGo's server-side defaults apply unless they're set. For example, `consolidateWalletUnspents()` no longer sends `numUnspentsToMake = 1` and `limit = 25` by default.
- `getWebhookPayload()` decodes the payload as an array by default.

## [2.1.3] - 2019-01-19

### Added

- `getWebhookPayload()` reads an incoming webhook request.

### Fixed

- `BitGoExpress::sendTransaction()` sends the amount as a string ([#6](https://github.com/neto737/BitGoSDK-PHP/pull/6)).

## [2.1.2] - 2018-06-01

### Changed

- Empty parameters are removed from request bodies.
- `toBTC()` and `toSatoshi()` are now static.

## [2.1.1] - 2018-04-30

### Fixed

- POST and PUT requests send a JSON body with a `Content-Type: application/json` header.

## [2.1] - 2018-04-23

### Added

- `BitGoExpress` client for BitGo Express: login, wallet generation, keychains, wallet sharing, transaction build/sign/send, send-to-many, unspent consolidation and fan-out, sweep and pending approvals.
- `CurrencyCode` enum.
- `BitGoSDK`: `getCurrentUserProfile()`, `getSessionInfo()`, `lockSession()`, `unlockSession()`, `logout()`, `toBTC()` and `toSatoshi()`.

## [2.0.1] - 2018-04-15

### Fixed

- DELETE requests send their parameters.

## [2.0] - 2018-04-15

### Added

- Wallets, addresses, transfers, unspents, keychains, webhooks, wallet sharing, policy rules, pending approvals, fee estimates and market data on BitGo API v2.
- `BitGoSDKInterface`.

### Changed

- **Breaking:** rewritten for BitGo API v2 with multi-coin support. The constructor now takes `(string $accessToken, string $coin = 'btc', bool $testNet = false)`.
- PSR-4 autoloading under the `neto737\BitGoSDK` namespace.
- License changed from LGPL-3.0 to GPL-3.0.

### Removed

- **Breaking:** the API v1 methods (`createAddress()`, `listWalletAddresses()`, `listWalletTransactions()`, `getAddressDetails()`, `getTransactionDetails()`, the label methods and others).

## [1.1.1] - 2017-11-15

### Added

- Bundled `cacert.pem` for TLS verification.

## [1.1] - 2017-11-15

### Changed

- Typed method parameters and general cleanup.

## [1.0.0] - 2017-08-20

### Added

- First release on Packagist: a BitGo API v1 client for wallets, addresses, labels, keychains and transactions. The `1.0` tag points to the same commit.

[Unreleased]: https://github.com/neto737/BitGoSDK-PHP/compare/2.2.3...HEAD
[2.2.3]: https://github.com/neto737/BitGoSDK-PHP/compare/2.2.2...2.2.3
[2.2.2]: https://github.com/neto737/BitGoSDK-PHP/compare/2.2.1...2.2.2
[2.2.1]: https://github.com/neto737/BitGoSDK-PHP/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/neto737/BitGoSDK-PHP/compare/2.1.9...2.2.0
[2.1.9]: https://github.com/neto737/BitGoSDK-PHP/compare/2.1.8...2.1.9
[2.1.8]: https://github.com/neto737/BitGoSDK-PHP/compare/2.1.7...2.1.8
[2.1.7]: https://github.com/neto737/BitGoSDK-PHP/compare/2.1.6...2.1.7
[2.1.6]: https://github.com/neto737/BitGoSDK-PHP/compare/2.1.5...2.1.6
[2.1.5]: https://github.com/neto737/BitGoSDK-PHP/compare/2.1.4...2.1.5
[2.1.4]: https://github.com/neto737/BitGoSDK-PHP/compare/2.1.3...2.1.4
[2.1.3]: https://github.com/neto737/BitGoSDK-PHP/compare/2.1.2...2.1.3
[2.1.2]: https://github.com/neto737/BitGoSDK-PHP/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/neto737/BitGoSDK-PHP/compare/2.1...2.1.1
[2.1]: https://github.com/neto737/BitGoSDK-PHP/compare/2.0.1...2.1
[2.0.1]: https://github.com/neto737/BitGoSDK-PHP/compare/2.0...2.0.1
[2.0]: https://github.com/neto737/BitGoSDK-PHP/compare/1.1.1...2.0
[1.1.1]: https://github.com/neto737/BitGoSDK-PHP/compare/1.1...1.1.1
[1.1]: https://github.com/neto737/BitGoSDK-PHP/compare/1.0.0...1.1
[1.0.0]: https://github.com/neto737/BitGoSDK-PHP/releases/tag/1.0.0
