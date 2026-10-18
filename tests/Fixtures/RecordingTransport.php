<?php

namespace neto737\BitGoSDK\Tests\Fixtures;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Internal\Transport;
use ReflectionProperty;

/**
 * A transport that records each request and answers with a canned response, so nothing goes over the network.
 */
final class RecordingTransport implements Transport
{
    /** @var list<CapturedRequest> */
    public array $requests = [];
    /** @var list<array{body: string, status: int, errno: int, error: string}> */
    private array $responses = [];

    /**
     * Install a new recording transport on $client.
     *
     * @param BitGoSDK|BitGoExpress $client
     */
    public static function on($client): self
    {
        $transport = new self();
        $property = new ReflectionProperty($client, 'transport');
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }
        $property->setValue($client, $transport);
        return $transport;
    }

    /**
     * Queue a response. Requests take queued responses in order, then get 200 with an empty JSON object.
     */
    public function respond(string $body, int $status = 200): self
    {
        $this->responses[] = ['body' => $body, 'status' => $status, 'errno' => 0, 'error' => ''];
        return $this;
    }

    /**
     * Queue a cURL failure.
     */
    public function fail(int $errno, string $error = ''): self
    {
        $this->responses[] = ['body' => '', 'status' => 0, 'errno' => $errno, 'error' => $error];
        return $this;
    }

    public function send(array $options): array
    {
        $this->requests[] = new CapturedRequest($options);
        return array_shift($this->responses) ?? ['body' => '{}', 'status' => 200, 'errno' => 0, 'error' => ''];
    }

    public function last(): CapturedRequest
    {
        if ($this->requests === []) {
            throw new \LogicException('No request was sent.');
        }
        return $this->requests[count($this->requests) - 1];
    }
}
