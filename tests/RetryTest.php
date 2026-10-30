<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use Http\Client\Exception\NetworkException;
use neto737\BitGoSDK\BitGo;
use neto737\BitGoSDK\Config;
use neto737\BitGoSDK\Exception\ApiException;
use neto737\BitGoSDK\Exception\NotFoundException;
use neto737\BitGoSDK\Exception\RateLimitException;
use neto737\BitGoSDK\Exception\ServerException;
use neto737\BitGoSDK\Exception\TransportException;
use neto737\BitGoSDK\Internal\Http;
use neto737\BitGoSDK\RetryPolicy;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Which failures are retried, how long the client waits, and when it gives up.
 */
final class RetryTest extends TestCase
{
    private const PATH = ['coin' => 'tbtc', 'walletId' => 'w1'];

    /** @var list<int> Each wait between attempts, in milliseconds */
    private array $sleeps = [];

    private function retrying(RetryPolicy $policy = new RetryPolicy(maxAttempts: 3, baseDelayMs: 100, maxDelayMs: 1000)): Http
    {
        return new Http(Config::testnet(self::TOKEN)->withRetryPolicy($policy), $this->client, $this->factory, $this->factory, function (int $ms): void {
            $this->sleeps[] = $ms;
        });
    }

    private function respondWithHeader(int $status, string $header, string $value): void
    {
        $this->client->addResponse($this->factory->createResponse($status)->withHeader($header, $value));
    }

    private function networkError(): void
    {
        $this->client->addException(new NetworkException('Connection reset', $this->factory->createRequest('GET', self::API)));
    }

    public function testGetThatFailsOnceSucceedsWithRetriesOn(): void
    {
        $this->respond(['error' => 'unavailable'], 503);
        $this->respond(['id' => 'w1', 'coin' => 'tbtc', 'label' => 'Hot']);
        $bitgo = new BitGo(Config::testnet(self::TOKEN)->withRetryPolicy(new RetryPolicy(baseDelayMs: 0)), $this->client, $this->factory, $this->factory);

        self::assertSame('w1', $bitgo->coin('tbtc')->wallet('w1')->get()['id']);
        self::assertCount(2, $this->requests());
    }

    public function testGetThatFailsOnceThrowsWithRetriesOff(): void
    {
        $this->respond(['error' => 'unavailable'], 503);
        $this->respond(['id' => 'w1', 'coin' => 'tbtc', 'label' => 'Hot']);

        try {
            $this->bitgo()->coin('tbtc')->wallet('w1')->get();
            self::fail('No exception was thrown.');
        } catch (ServerException) {
            self::assertCount(1, $this->requests());
        }
    }

    /**
     * @return array<string, array{int}>
     */
    public static function retriedStatuses(): array
    {
        return ['HTTP 429' => [429], 'HTTP 500' => [500], 'HTTP 502' => [502], 'HTTP 503' => [503], 'HTTP 504' => [504]];
    }

    #[DataProvider('retriedStatuses')]
    public function testRetriesAStatusThatMayPass(int $status): void
    {
        $this->respond([], $status);
        $this->respond(['ok' => true]);

        self::assertSame(['ok' => true], $this->retrying()->get('{coin}/wallet/{walletId}', self::PATH));
        self::assertCount(2, $this->requests());
        self::assertCount(1, $this->sleeps);
    }

    public function testRetriesANetworkError(): void
    {
        $this->networkError();

        self::assertSame([], $this->retrying()->get('{coin}/wallet/{walletId}', self::PATH));
        self::assertCount(2, $this->requests());
    }

    /**
     * @return array<string, array{int, class-string<ApiException>}>
     */
    public static function statusesThatAreNotRetried(): array
    {
        return ['HTTP 400' => [400, ApiException::class], 'HTTP 404' => [404, NotFoundException::class]];
    }

    /**
     * @param class-string<ApiException> $class
     */
    #[DataProvider('statusesThatAreNotRetried')]
    public function testDoesNotRetryAClientError(int $status, string $class): void
    {
        $this->respond([], $status);

        try {
            $this->retrying()->get('{coin}/wallet/{walletId}', self::PATH);
            self::fail('No exception was thrown.');
        } catch (ApiException $e) {
            self::assertSame($class, $e::class);
            self::assertCount(1, $this->requests());
            self::assertSame([], $this->sleeps);
        }
    }

    public function testNeverRetriesAPost(): void
    {
        $this->respond([], 503);

        try {
            $this->retrying()->post('{coin}/wallet/{walletId}/address', self::PATH);
            self::fail('No exception was thrown.');
        } catch (ServerException) {
            self::assertCount(1, $this->requests());
        }
    }

    public function testNeverRetriesAPostAfterANetworkError(): void
    {
        $this->networkError();

        try {
            $this->retrying()->post('{coin}/wallet/{walletId}/address', self::PATH);
            self::fail('No exception was thrown.');
        } catch (TransportException) {
            self::assertCount(1, $this->requests());
        }
    }

    public function testRetriesPutAndDeleteWithTheSameBody(): void
    {
        $this->respond([], 503);
        $this->respond();
        $this->respond([], 503);
        $http = $this->retrying();

        $http->put('{coin}/wallet/{walletId}', self::PATH, ['label' => 'Renamed']);
        $http->delete('{coin}/wallet/{walletId}/webhooks', self::PATH, ['id' => 'h1']);

        $requests = $this->requests();
        self::assertCount(4, $requests);
        self::assertRequest($requests[0], 'PUT', self::API . 'tbtc/wallet/w1', ['label' => 'Renamed']);
        self::assertRequest($requests[1], 'PUT', self::API . 'tbtc/wallet/w1', ['label' => 'Renamed']);
        self::assertRequest($requests[2], 'DELETE', self::API . 'tbtc/wallet/w1/webhooks', ['id' => 'h1']);
        self::assertRequest($requests[3], 'DELETE', self::API . 'tbtc/wallet/w1/webhooks', ['id' => 'h1']);
    }

    public function testGivesUpAfterTheLastAttempt(): void
    {
        $this->respond(['error' => 'first'], 503);
        $this->respond(['error' => 'second'], 503);
        $this->respond(['error' => 'third'], 503);
        $http = $this->retrying();

        try {
            $http->get('{coin}/wallet/{walletId}', self::PATH);
            self::fail('No exception was thrown.');
        } catch (ServerException $e) {
            self::assertSame('BitGo returned HTTP 503: third', $e->getMessage());
            self::assertCount(3, $this->requests());
            self::assertCount(2, $this->sleeps);
            self::assertSame($e->getResponse(), $http->lastResponse());
        }
    }

    public function testGivesUpOnANetworkErrorAfterTheLastAttempt(): void
    {
        $http = $this->retrying(new RetryPolicy(maxAttempts: 2, baseDelayMs: 0));
        $this->networkError();
        $this->networkError();

        try {
            $http->get('{coin}/wallet/{walletId}', self::PATH);
            self::fail('No exception was thrown.');
        } catch (TransportException) {
            self::assertCount(2, $this->requests());
            self::assertNull($http->lastResponse());
        }
    }

    public function testBackoffDoublesUpToTheMaximumDelay(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->respond([], 503);
        }

        try {
            $this->retrying(new RetryPolicy(maxAttempts: 5, baseDelayMs: 100, maxDelayMs: 250))->get('{coin}/wallet/{walletId}', self::PATH);
            self::fail('No exception was thrown.');
        } catch (ServerException) {
            self::assertCount(4, $this->sleeps);
            foreach ([100, 200, 250, 250] as $i => $bound) {
                self::assertGreaterThanOrEqual(0, $this->sleeps[$i]);
                self::assertLessThanOrEqual($bound, $this->sleeps[$i]);
            }
        }
    }

    public function testFollowsRetryAfterInSeconds(): void
    {
        $this->respondWithHeader(429, 'Retry-After', '1');

        $this->retrying()->get('{coin}/wallet/{walletId}', self::PATH);

        self::assertSame([1000], $this->sleeps);
    }

    public function testFollowsRetryAfterAsADate(): void
    {
        $this->respondWithHeader(503, 'Retry-After', 'Wed, 21 Oct 2015 07:28:00 GMT');

        $this->retrying()->get('{coin}/wallet/{walletId}', self::PATH);

        self::assertSame([0], $this->sleeps, 'A date in the past means retry now.');
    }

    public function testFallsBackToBackoffWhenRetryAfterIsNotValid(): void
    {
        $this->respondWithHeader(429, 'Retry-After', 'soon-ish');

        $this->retrying()->get('{coin}/wallet/{walletId}', self::PATH);

        self::assertCount(1, $this->sleeps);
        self::assertLessThanOrEqual(100, $this->sleeps[0]);
    }

    public function testGivesUpWhenRetryAfterIsLongerThanTheMaximumDelay(): void
    {
        $this->respondWithHeader(429, 'Retry-After', '3600');

        try {
            $this->retrying()->get('{coin}/wallet/{walletId}', self::PATH);
            self::fail('No exception was thrown.');
        } catch (RateLimitException) {
            self::assertCount(1, $this->requests());
            self::assertSame([], $this->sleeps);
        }
    }
}
