<?php

namespace neto737\BitGoSDK\Tests\Fixtures;

/**
 * One request a client handed to its transport, read back from the cURL options.
 */
final class CapturedRequest
{
    public string $method;
    public string $url;
    /** @var list<string> */
    public array $headers;
    /** The raw body, or null if none was set. */
    public ?string $body;
    /** @var array<int, mixed> */
    public array $options;

    /**
     * @param array<int, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        $this->method = $options[CURLOPT_CUSTOMREQUEST] ?? (!empty($options[CURLOPT_POST]) ? 'POST' : 'GET');
        $this->url = $options[CURLOPT_URL];
        $this->headers = $options[CURLOPT_HTTPHEADER] ?? [];
        $this->body = $options[CURLOPT_POSTFIELDS] ?? null;
    }

    /**
     * The body decoded as JSON, or null if there is none.
     *
     * @return mixed
     */
    public function json()
    {
        return $this->body === null ? null : json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
    }
}
