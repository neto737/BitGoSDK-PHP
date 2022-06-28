<?php

namespace neto737\BitGoSDK\Authentication;

use Exception;

class Environment {

    private $env;
    private $walletId;
    private $currency;

    public function __construct(string $env, string $walletId, string $currency) {
        $this->env = $env;

        $this->setWalletId($walletId);

        $this->currency = $currency;
    }

    private function setWalletId(string $walletId): void {
        if (preg_match('/^[0-9a-f]{32}$/', $walletId)) {
            $this->walletId = $walletId;
        } else {
            throw new Exception('Invalid wallet id format.');
        }
    }

    public function getEnvironment(): string {
        return $this->env;
    }

    public function getWalletId(): string {
        return $this->walletId;
    }

    public function getCurrency(): string {
        return $this->currency;
    }
}
