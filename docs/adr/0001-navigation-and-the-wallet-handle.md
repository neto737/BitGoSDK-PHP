# 0001. Navigation and the wallet handle

- **Status:** Accepted
- **Date:** 2026-10-29

## Context

BitGo's REST paths are a hierarchy: `/api/v2/{coin}/wallet/{walletId}/address/{address}`. Most calls need a coin, many need a wallet, and some need neither (the user, enterprises) or only a coin (block webhooks, the wallet list).

2.x put about 75 methods on one class and kept the coin and wallet ID as mutable state on the client. Switching wallets meant changing that state, and request params were also kept on the client between calls, so a call could resend the previous call's params. The earlier `v3` prototype fixed the coin and wallet ID per client in an `Environment`, which made non-wallet endpoints and working with several wallets awkward.

BitGo's own SDK, BitGoJS, goes from the coin to its wallets to a wallet. It also loads wallet objects that hold the wallet's data, has a very large `Wallet` class, and takes options objects as arguments.

## Decision

- **Nouns navigate, verbs send.** Methods named after a thing (`coin()`, `wallet()`, `wallets()`, `addresses()`, `webhooks()`, `blockWebhooks()`) only build a handle and never send a request. Methods named after an action (`get()`, `create()`, `update()`, `remove()`…) send exactly one request each time they're called.
- **`list()`** returns a lazy `Paginator` that fetches one batch per request as you iterate and follows BitGo's `nextBatchPrevId`.
- **The wallet is a handle only.** `$coin->wallet($id)` holds the coin and the ID, never the wallet's data. `$wallet->get()` fetches fresh data on every call.
- **Where a method goes.** An action on the wallet itself goes on `Wallet`. Each path segment under the wallet gets its own small class (`Wallet\Addresses`, `Wallet\Webhooks`), and the same applies under the coin (`Coin\Wallets`, `Coin\BlockWebhooks`).
- **Handles are immutable.** The coin and IDs are passed down on navigation, not set on the client, so every call carries its own.
- **Arguments** are named arguments, plus `array $extra = []` on every verb for params BitGo adds before the SDK names them. Named arguments win over `$extra`, and `null` means "not sent".
- **Names come from BitGo, mechanics don't.** Method and param names follow the REST API and BitGoJS (`coin`, `wallets`, `sendMany`, `walletPassphrase`). Loaded wallet objects, one large `Wallet` class and options-object arguments are not copied.
- **A verb that reads a sub-resource is named as a verb**, such as `getOwnershipProof()`, so it isn't mistaken for navigation.

```php
$wallet = $bitgo->coin('tbtc')->wallet($walletId);          // no request
$wallet->get();                                             // GET /tbtc/wallet/{walletId}
$wallet->addresses()->create(label: 'Deposit 42');          // POST /tbtc/wallet/{walletId}/address
foreach ($wallet->addresses()->list() as $address) { … }    // one GET per batch
```

## Consequences

- No state carries over between calls, and one client can work with any number of coins and wallets at once.
- Building a handle can't fail on the network. It only checks the IDs it is given (see [0005](0005-path-escaping.md)), so a wrong wallet ID surfaces as a `NotFoundException` on the first verb, not on `wallet()`.
- Reading a wallet's label or balance always costs a request. Callers who need the data more than once keep the array that `get()` returned.
- There are many small classes instead of a few large ones. Each new path segment means a new class and a navigation method, never more methods on `Wallet`.
- Iterating a `Paginator` twice fetches everything again.
- BitGo Express, when it's added, gets its own entry point with the same shape, since it has a different host and credentials.
