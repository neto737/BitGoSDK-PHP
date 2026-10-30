# 0004. The exception hierarchy

- **Status:** Accepted
- **Date:** 2026-10-29

## Context

2.x returned `null` or a raw error array when a request failed, so a failed call looked like an empty result unless the caller checked. The earlier `v3` prototype returned a string from methods declared to return an array, which caused a `TypeError`. It didn't catch server or connection errors at all, and treated any status other than 200 as a failure, including 201 and 202.

Money moves through this library, so a failure must never pass as a result, and callers need to tell a bad token from a missing wallet, a rate limit and a network problem without parsing messages.

## Decision

Every failure throws. Everything the SDK throws implements one interface:

```
BitGoException (interface)
├── ApiException                    BitGo answered with a non-2xx status
│   ├── AuthenticationException     401, 403
│   ├── NotFoundException           404
│   ├── RateLimitException          429
│   ├── ServerException             5xx
│   └── UnexpectedResponseException a 2xx whose body isn't a JSON object
├── TransportException              no response: network error or timeout
└── InvalidArgumentException        a value rejected before anything is sent
```

- **`BitGoException` is an interface**, so each class can extend the matching PHP base: `ApiException` and `TransportException` extend `\RuntimeException`, and `InvalidArgumentException` extends PHP's `\InvalidArgumentException`.
- **Every 2xx is a success**, not only 200.
- **`ApiException` carries the response:** `getStatusCode()`, BitGo's error name (`getErrorName()`), the request ID (`getRequestId()`), the decoded body (`getBody()`) and the PSR-7 response (`getResponse()`). Its message includes the status and BitGo's error message. `ApiException::fromResponse()` picks the subclass from the status.
- **`TransportException`** wraps the PSR-18 client's exception as its previous exception.
- **`InvalidArgumentException`** is for values the SDK can reject without asking BitGo: an empty path ID, `.` or `..`, a body that can't be encoded as JSON, an `Amount` that isn't a number, or a call with nothing to identify what to act on.
- With retries on (see [0006](0006-retries-are-opt-in-and-never-post.md)), the exception is the one for the last attempt.

## Consequences

- `catch (BitGoException $e)` catches everything the SDK throws, and nothing else.
- Callers handle the common cases by type (an expired token, a missing wallet, a rate limit) and anything else by `getStatusCode()` and `getErrorName()`. A 400 validation error is a plain `ApiException`.
- More subclasses can be added later without breaking anyone, as long as each extends the class that statuses map to today.
- Programming errors inside the SDK, such as a path template with no value for a placeholder, throw PHP's `\LogicException` and deliberately don't implement `BitGoException`.
