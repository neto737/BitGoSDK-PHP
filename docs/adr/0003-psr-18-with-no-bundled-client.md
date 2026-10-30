# 0003. PSR-18 with no bundled HTTP client

- **Status:** Accepted
- **Date:** 2026-10-30

## Context

2.x called cURL directly. It shipped its own CA bundle, which went stale, forced IPv4 on every request and set no timeout. The earlier `v3` prototype depended on Guzzle, turned off TLS certificate verification with `'verify' => false`, and echoed every request's URI and timing to stdout.

An SDK that brings its own HTTP client forces that client's version on the application, can conflict with the one already installed, and makes every transport setting (timeouts, proxies, TLS, CA bundles) something the SDK has to expose and get right.

PSR-18 (`psr/http-client`) and PSR-17 (`psr/http-factory`) define a client and factories that any implementation can provide. PSR-18 has no way to set timeouts or TLS options: those belong to the implementation.

## Decision

- **The SDK depends only on the PSR-18 and PSR-17 interfaces.** It requires `psr/http-client-implementation` and `psr/http-factory-implementation`, so Composer insists that one is installed, but it doesn't choose which.
- **The caller may pass a client and factories** to `new BitGo(...)`. If they don't, `php-http/discovery` finds the ones installed in the project, such as Guzzle or Symfony HttpClient. The discovery Composer plugin stays disabled, so nothing is installed behind the caller's back.
- **The SDK never builds or configures a client.** `Config` has no timeout, proxy, TLS or CA bundle options. Those are set on the client the caller passes.
- **`Config` accepts only `https://` base URLs**, since the access token is sent with every request.
- **The SDK never writes output.** No debug printing and no logging of its own.
- **Every request carries `User-Agent: BitGoSDK-PHP/{version}`**, from `BitGo::VERSION`.

## Consequences

- The SDK works with whatever client an application already uses, with no version conflicts, and `ext-curl` isn't required.
- TLS verification is whatever the caller's client does, which is on by default in every mainstream client. The SDK can't turn it off.
- **A discovered client runs with its library's defaults.** Guzzle's default timeout is 0, which means none, so a hung connection can block forever. The README has to tell users to pass a client with a timeout.
- If no client or factory is installed or passed, the constructor throws `InvalidArgumentException` with an install hint, instead of failing on the first request.
- Tests run against `php-http/mock-client`, so they check the exact requests sent and never touch the network.
