<?php

/**
 *  ____  _ _    ____      ____  ____  _  __
 * | __ )(_) |_ / ___| ___/ ___||  _ \| |/ /
 * |  _ \| | __| |  _ / _ \___ \| | | | ' /
 * | |_) | | |_| |_| | (_) |__) | |_| | . \
 * |____/|_|\__|\____|\___/____/|____/|_|\_\
 *
 * @package BitGoSDK PHP
 * @author  Neto Melo <neto737@live.com>
 * @license https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace neto737\BitGoSDK;

use neto737\BitGoSDK\Exception\InvalidArgumentException;

/**
 * When to send a request again after a 429, a 5xx or a network error. Pass it to Config::withRetryPolicy().
 *
 * Only GET, PUT and DELETE requests are retried. A POST is never sent twice, because it can create something or
 * move funds, and a POST whose response was lost may already have done so.
 *
 * Between attempts the client waits a random time from 0 up to $baseDelayMs, doubling the upper bound after each
 * attempt, and never more than $maxDelayMs. When BitGo sends a Retry-After header, the client waits that long
 * instead, or doesn't retry at all if it asks for more than $maxDelayMs.
 *
 * ```php
 * $config = Config::production($accessToken)->withRetryPolicy(new RetryPolicy(maxAttempts: 4));
 * ```
 */
final readonly class RetryPolicy
{
    /**
     * @param int $maxAttempts  How many times to send a request in all, the first time included. 1 means no retries.
     * @param int $baseDelayMs  The upper bound of the wait before the first retry, in milliseconds
     * @param int $maxDelayMs   The longest the client waits before a retry, in milliseconds
     * @throws InvalidArgumentException if $maxAttempts is below 1 or a delay is negative
     */
    public function __construct(public int $maxAttempts = 3, public int $baseDelayMs = 500, public int $maxDelayMs = 30_000)
    {
        if ($maxAttempts < 1) {
            throw new InvalidArgumentException('maxAttempts must be 1 or more, got ' . $maxAttempts . '.');
        }
        if ($baseDelayMs < 0 || $maxDelayMs < 0) {
            throw new InvalidArgumentException('Retry delays can\'t be negative.');
        }
    }

    /**
     * Send every request once. This is the default.
     */
    public static function none(): self
    {
        return new self(1, 0, 0);
    }

    /**
     * How long to wait before retrying after a response, or null to give up because its Retry-After is too long.
     *
     * @internal Used by the HTTP layer.
     * @param int $attempt          The attempt that just failed, starting at 1
     * @param string $retryAfter    The response's Retry-After header, or '' if there was none
     */
    public function delayMs(int $attempt, string $retryAfter): ?int
    {
        $requested = self::retryAfterMs($retryAfter);
        if ($requested === null) {
            return $this->backoffMs($attempt);
        }
        return $requested <= $this->maxDelayMs ? (int) $requested : null;
    }

    /**
     * How long to wait before retrying when there's no Retry-After to follow, such as after a network error.
     *
     * @internal Used by the HTTP layer.
     * @param int $attempt  The attempt that just failed, starting at 1
     */
    public function backoffMs(int $attempt): int
    {
        // Full jitter: a random wait up to an exponentially growing bound, so many clients don't retry in step.
        return random_int(0, (int) min($this->maxDelayMs, $this->baseDelayMs * 2 ** min($attempt - 1, 30)));
    }

    /**
     * Retry-After in milliseconds: either a number of seconds or an HTTP date. null if it's missing or not valid.
     */
    private static function retryAfterMs(string $value): ?float
    {
        if ($value === '') {
            return null;
        }
        if (ctype_digit($value)) {
            return (float) $value * 1000;
        }
        $time = strtotime($value);
        return $time === false ? null : max(0, $time - time()) * 1000;
    }
}
