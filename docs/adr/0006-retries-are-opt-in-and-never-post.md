# 0006. Retries are opt-in and never apply to `POST`

- **Status:** Accepted
- **Date:** 2026-10-30

## Context

BitGo rate-limits clients with 429 responses, sometimes answers with a 5xx, and requests can fail on the network. Retrying such a request after a pause often succeeds.

A retry is only safe when sending the request twice has the same effect as sending it once. BitGo's sends, transfers, wallet and address creation and approvals are `POST`s. If a `POST` reached BitGo but its response was lost, sending it again could move funds twice. A client can't tell that case apart from a request that never arrived.

Retries also change how long a call can take and multiply load during an outage, so they're a choice for the application, not a default.

## Decision

- **Retries are off by default.** `Config` holds a `RetryPolicy`, and `RetryPolicy::none()` (one attempt) is the default. Callers opt in with `Config::withRetryPolicy(new RetryPolicy(maxAttempts: 3, baseDelayMs: 500, maxDelayMs: 30_000))`.
- **Only `GET`, `PUT` and `DELETE` are retried.** `POST` is sent once, whatever the policy says.
- **What is retried:** a 429, a 5xx and a `TransportException`. Any other status is returned or thrown at once.
- **The wait between attempts** is exponential backoff with full jitter: a random time up to `baseDelayMs × 2^(attempt − 1)`, capped at `maxDelayMs`.
- **`Retry-After` is followed** on a 429 or 5xx, as seconds or as an HTTP date. If it asks for longer than `maxDelayMs`, the request isn't retried and the exception is thrown at once.
- **Each attempt is a new request**, since sending can consume the body stream. `BitGo::lastResponse()` and any exception reflect the last attempt.

## Consequences

- A caller who turns retries on gets them for every read and update, with no change to their code.
- A caller who wants to retry a `POST` has to do it themselves, knowing how their own flow avoids a double send.
- `PUT` and `DELETE` are treated as idempotent, which holds for BitGo's endpoints today, such as updating an address or removing a webhook. A future endpoint where it doesn't hold must be excluded explicitly.
- With retries on, a call can block the PHP process for up to the sum of the waits: at most `(maxAttempts − 1) × maxDelayMs`, plus the time of each attempt.
