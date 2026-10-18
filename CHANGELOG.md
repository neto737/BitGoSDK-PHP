# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Entries for 2.2.3 and earlier were reconstructed from the git history in October 2026, so they are high level. Release dates are the dates of the tagged commits.

## [Unreleased]

### Added

- `BitGoSDK::toBaseUnits()` and `fromBaseUnits()` convert between decimal amounts and base units exactly, at any number of decimals (for example 18 for ETH).
- Request timeouts: `setTimeout()` and `setConnectTimeout()` on both clients. The defaults are a 10 second connect timeout and a 60 second (`BitGoSDK`) or 310 second (`BitGoExpress`) request timeout.
- `BitGoExpress` can reach Express over TLS. Prefix the hostname with `https://`, trust a custom or self-signed certificate with `setCaFile()`, or, for local testing only, turn off verification with `setVerifyTls(false)`.
- `setForceIPv4()` on both clients, to get back the old IPv4-only behaviour.
- `BitGoSDK::setCaFile()`, to trust a custom CA file, for example behind a TLS-inspecting proxy.
- Both clients send a `User-Agent: BitGoSDK-PHP/<version>` header. The version is in `BitGoSDK::VERSION`.
- `BitGoExpress::pingExpress()`, which checks that Express is running without contacting BitGo.
- `BitGoExpress::fanoutWalletUnspents()`, replacing the misspelled `funoutWalletUnspents()`.
- `AddressType` constants named after each chain's script type: `P2SH_P2WSH_*` (10/11), `P2WSH_*` (20/21) and `P2TR_*` (30/31).
- `CurrencyCode` constants for Aptos, Arbitrum, Avalanche C-Chain and P-Chain, BNB Smart Chain, Cardano, Cosmos, Dogecoin, NEAR, Optimism, Polkadot, Polygon, Solana, Sui and TON with their testnets. Also the Bitcoin Gold testnet, Bitcoin testnet4 and signet, the Ethereum Hoodi and Sepolia testnets, and the USDT, USDC, DAI, LINK, WBTC, UNI, AAVE, GLM, REPv2 and KNC2 tokens.
- `CHANGELOG.md`, `SECURITY.md` and a full `CONTRIBUTING.md` (branch model, local setup, coding style, tests and release process).
- `.editorconfig` and `.gitattributes`.
- Dev tooling: PHPUnit 9.6, PHPStan (level 6, with a baseline for the existing code) and PHP-CS-Fixer (PSR-12).
- A GitHub Actions CI workflow: `php -l` and PHPUnit on PHP 7.4 to 8.5 with lowest and highest dependencies, plus PHPStan and a coding-standard check.
- Composer scripts: `composer test`, `composer analyse`, `composer cs` and `composer cs:fix`.
- `homepage` and `support` (issues, source, security policy) in `composer.json`.

### Changed

- **`0`, `false` and `''` params are now sent.** Only `null` params are dropped. Before, `chain => 0`, `minConfirms => 0`, `numConfirmations => 0` and every `false` flag were silently left out. If you passed `0` or `false` to mean "leave it unset", pass `null` instead. GET query strings now send booleans as `true`/`false` instead of `1`/`0`.
- **Errors now come back as `['error' => …, 'status' => …]`.** A cURL failure, a non-2xx status or an invalid JSON body used to return `null` or BitGo's raw error body. Any other fields BitGo sent are kept, so `isset($r['error'])` checks keep working. `status` is the HTTP status, or `0` if no response arrived. `buildTransaction()` and `signBuildTransaction()` return the same fields as an object.
- Amounts in `sendTransaction()`, `sendTransactionToMany()` and `buildTransaction()` accept an `int` or a string of digits, and are always sent as strings, so 18-decimal amounts no longer overflow `PHP_INT_MAX`. `toSatoshi()` accepts strings and no longer breaks under locales that use a decimal comma. `toBTC()` no longer converts through a float.
- Requests no longer force IPv4. cURL uses IPv4 or IPv6 as the system allows. A local address bound with `setRequestIP()` decides the IP version.
- `BitGoSDK::setRequestIP()` returns `true` if the IP was applied, and `false` on testnet or for an invalid IP.
- **TLS certificates are checked against the system CA store.** The bundled 2022 `cacert.pem` is gone. If PHP finds no system CA store, the Mozilla bundle from the new `composer/ca-bundle` dependency is used.
- `ext-bcmath` is no longer required.
- **The minimum PHP version is now 7.4** (was 7.0).
- The project is now licensed under MIT. 2.2.3 and earlier were released under GPL-3.0. The file headers say so too.
- The README lists the current requirements, links to the docs on developers.bitgo.com, explains how to reach Express over TLS and names the minimum Express version (14.1.3).
- `src/` and `examples/` were reformatted to PSR-12. This is whitespace and brace placement only, with no behaviour change.
- The examples run without notices on PHP 8.4, from any directory. They pass BTC amounts to `toSatoshi()` as strings and check for errors after `unlockSession()`.
- Composer dist archives no longer include `tests/`, `examples/`, `docs/` or dotfiles. The examples are still available in the repository.

### Deprecated

These still work and will be removed in 3.0.

- `BitGoExpress::funoutWalletUnspents()`. Use `fanoutWalletUnspents()`.
- `BitGoSDK::addBlockWebhook()`, `listBlockWebhooks()` and `removeBlockWebhook()`. They call the same endpoint as `addUserWebhook()`, `listUserWebhooks()` and `removeUserWebhook()`, so use those instead. The deprecated methods still send any type other than `wallet_confirmation` as `block`. The `User` methods send the type as given.
- `AddressType::P2SH_DEPOSIT` and `P2SH_CHANGE`. Chains 10 and 11 are SegWit wrapped in P2SH. Use `P2SH_P2WSH_*`, or `LEGACY_*` for plain P2SH.
- `CurrencyCode::AVAX` and `AVAX_TESTNET` (`avax` is not a BitGo coin code; use `AVALANCHE_C_CHAIN`), and `ETHEREUM_TESTNET` (`teth` is the retired Kovan testnet; use `ETHEREUM_HOODI_TESTNET` or `ETHEREUM_SEPOLIA_TESTNET`).
- `CurrencyCode::OMISE_GO`, `GOLEM`, `AUGUR_REP`, `KYBER_NETWORK`, `QRL`, `TENXPAY` and `BREAD`, for tokens that migrated or wound down.

### Fixed

- Request params no longer leak from one call to the next. Methods that set no params of their own, such as `getWalletByAddress()`, used to resend the previous call's params, including an `otp` from `unlockSession()`.
- `setVelocityPolicyRule()`, `updateVelocityPolicyRule()` and `removePolicyRule()` put the rule ID where the wallet ID belongs in the URL.
- `BitGoExpress::login()` stores the returned access token, and later calls send their `Authorization` and `Content-Type` headers again.
- Testnet wallets get their coin-specific params (`gasPrice`, `enterprise`, `segwit`, `dt`, `memoId` and others), and the UTXO coin list no longer includes Stacks.
- `generateWallet()` no longer sends `gasPrice: 0` for non-ETH coins.
- No deprecation notices on PHP 8.2 and later: every property is declared, no parameter is implicitly nullable, and `curl_close()` is no longer called.
- The `ping()` and `createWalletAddress()` docblocks describe what the code does, and docblock typos are fixed.

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
