<?php

namespace neto737\BitGoSDK\Authentication;

class Authentication
{
    private $apikey;

    public function __construct(string $apikey)
    {
        $this->apikey = $apikey;
    }

    public function setApiKey(string $apikey): void
    {
        $this->apikey = $apikey;
    }

    public function getApiKey(): string
    {
        return $this->apikey;
    }
}
