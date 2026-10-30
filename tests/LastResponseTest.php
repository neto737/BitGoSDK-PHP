<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use Http\Client\Exception\NetworkException;
use neto737\BitGoSDK\Exception\ApiException;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use neto737\BitGoSDK\Exception\TransportException;

/**
 * BitGo::lastResponse(): the status and headers of a call whose verb returned only the body.
 */
final class LastResponseTest extends TestCase
{
    public function testIsNullBeforeAnyRequest(): void
    {
        self::assertNull($this->bitgo()->lastResponse());
    }

    public function testHasTheStatusAndHeadersOfASuccessfulCreate(): void
    {
        $this->client->addResponse($this->factory->createResponse(201)
            ->withHeader('Date', 'Fri, 30 Oct 2026 04:00:00 GMT')
            ->withBody($this->factory->createStream('{"id":"a1"}')));
        $bitgo = $this->bitgo();

        self::assertSame(['id' => 'a1'], $bitgo->coin('tbtc')->wallet('w1')->addresses()->create());
        self::assertSame(201, $bitgo->lastResponse()?->getStatusCode());
        self::assertSame('Fri, 30 Oct 2026 04:00:00 GMT', $bitgo->lastResponse()->getHeaderLine('Date'));
    }

    public function testIsTheLatestResponseAcrossHandles(): void
    {
        $this->respond(['id' => 'w1', 'coin' => 'tbtc', 'label' => 'One']);
        $this->respond(['id' => 'w2', 'coin' => 'tbtc', 'label' => 'Two'], 202);
        $bitgo = $this->bitgo();
        $coin = $bitgo->coin('tbtc');

        $coin->wallet('w1')->get();
        $coin->wallet('w2')->get();

        self::assertSame(202, $bitgo->lastResponse()?->getStatusCode());
    }

    public function testIsTheLatestBatchWhileIteratingAList(): void
    {
        $this->respond(['addresses' => [['id' => 'a1']], 'nextBatchPrevId' => 'a1']);
        $this->respond(['addresses' => [['id' => 'a2']]]);
        $bitgo = $this->bitgo();

        $seen = [];
        foreach ($bitgo->coin('tbtc')->wallet('w1')->addresses()->list() as $address) {
            $seen[$address['id']] = (string) $bitgo->lastResponse()?->getBody();
        }

        self::assertSame(['a1' => '{"addresses":[{"id":"a1"}],"nextBatchPrevId":"a1"}', 'a2' => '{"addresses":[{"id":"a2"}]}'], $seen);
    }

    public function testIsTheErrorResponseAfterAnApiError(): void
    {
        $this->respond(['error' => 'wallet not found'], 404);
        $bitgo = $this->bitgo();

        try {
            $bitgo->coin('tbtc')->wallet('w404')->get();
            self::fail('No exception was thrown.');
        } catch (ApiException $e) {
            self::assertSame($e->getResponse(), $bitgo->lastResponse());
        }
    }

    public function testIsNullAfterARequestThatGotNoResponse(): void
    {
        $this->respond(['id' => 'w1', 'coin' => 'tbtc', 'label' => 'One']);
        $bitgo = $this->bitgo();
        $wallet = $bitgo->coin('tbtc')->wallet('w1');
        $wallet->get();
        self::assertNotNull($bitgo->lastResponse());
        // Queued only now, since the mock client throws queued exceptions before it returns queued responses.
        $this->client->addException(new NetworkException('Connection refused', $this->factory->createRequest('GET', self::API)));

        try {
            $wallet->get();
            self::fail('No exception was thrown.');
        } catch (TransportException) {
            self::assertNull($bitgo->lastResponse());
        }
    }

    public function testIsKeptWhenACallIsRejectedBeforeSending(): void
    {
        $this->respond(['id' => 'w1', 'coin' => 'tbtc', 'label' => 'One']);
        $bitgo = $this->bitgo();
        $addresses = $bitgo->coin('tbtc')->wallet('w1')->addresses();
        $bitgo->coin('tbtc')->wallet('w1')->get();
        $response = $bitgo->lastResponse();

        try {
            $addresses->get('');
            self::fail('No exception was thrown.');
        } catch (InvalidArgumentException) {
            self::assertNotNull($response);
            self::assertSame($response, $bitgo->lastResponse());
        }
    }
}
