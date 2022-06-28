<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands;

use Psr\Http\Message\ResponseInterface;

abstract class Command implements CommandInterface {

    public function process(ResponseInterface $response) {
        if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody()->getContents(), true);
        }

        return null;
    }

    public function getPathParam(): string {
        return '';
    }

    public function getQueryParameters(): array {
        return [];
    }

    public function getBodyParameters(): array {
        return [];
    }

    public function validateId(string $id): ?string {
        if (preg_match('/^[0-9a-f]{32}$/', $id)) {
            return $id;
        }
    }
}
