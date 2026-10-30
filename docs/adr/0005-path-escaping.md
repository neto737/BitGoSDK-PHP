# 0005. Path escaping

- **Status:** Accepted
- **Date:** 2026-10-28

## Context

Coin codes, wallet IDs, addresses and other IDs go into the request path, often straight from user input or a webhook. In 2.3.0 and earlier they were concatenated into the path unescaped. An ID containing `/`, `..`, `?` or `#` could point an authenticated request at a different BitGo endpoint or add query params, for example `getWalletTransaction('../../../user/me')`. This was fixed in 2.3.1 ([GHSA-qr36-39c2-hp84](https://github.com/neto737/BitGoSDK-PHP/security/advisories/GHSA-qr36-39c2-hp84)).

The earlier `v3` prototype filled every placeholder of a path from a single value, so a path couldn't contain two IDs.

XRP and XLM addresses can carry a destination tag or memo ID after a `?`, as in `r…?dt=123` or `G…?memoId=123`. 2.3.1 sends the tag as a query string to keep its wire format unchanged. Read-only checks on BitGo's testnet on 2026-10-28 showed that BitGo resolves the whole address escaped into the path (`r…%3Fdt%3D123`) to the same tagged address, for getting, updating and looking up a wallet by address.

## Decision

- **Paths are built from a template and named params**, such as `{coin}/wallet/{walletId}/address/{address}` with `['coin' => …, 'walletId' => …, 'address' => …]`, by the internal `Path::build()`. No code concatenates IDs into a path by hand.
- **Each value is escaped as exactly one path segment** with `rawurlencode()`, so `/`, `?`, `#` and `%` can't leave the endpoint or add a query.
- **An empty value, `.` or `..` throws `InvalidArgumentException` before anything is sent.** Escaping leaves them unchanged, and they would still change the endpoint.
- **Handles check their IDs when they're built.** `coin()` and `wallet()` reject a bad ID on navigation, not on the first verb.
- **Addresses are escaped whole, tags included.** The XRP/XLM special case from 2.3.1 is gone.
- **A placeholder with no value is a bug in the SDK** and throws PHP's `\LogicException`.
- Query strings are built from named params with `http_build_query()`, never by hand.

## Consequences

- An ID can't change which endpoint a request reaches, whatever it contains, and every new endpoint gets this by using a template.
- Tagged XRP and XLM addresses go out as `r…%3Fdt%3D123` instead of 2.3.1's `r…?dt=123`. BitGo treats both the same, and the CHANGELOG says so.
- Every value goes through the same escaping, so a coin code with a reserved character, such as a token code with `:`, is sent escaped (`%3A`). This hasn't been checked against BitGo yet. If BitGo rejects it, `Path` has to allow that character for the coin segment.
