<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands;

use Psr\Http\Message\ResponseInterface;

interface CommandInterface
{
    public function getRequestMethod(): string;

    public function getEndpoint(): string;

    public function getPathParam(): string;

    public function getQueryParameters(): array;

    public function getBodyParameters(): array;

    public function process(ResponseInterface $response);
}
