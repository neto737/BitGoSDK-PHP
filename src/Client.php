<?php

namespace neto737\BitGoSDK;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use neto737\BitGoSDK\Authentication\Authentication;
use neto737\BitGoSDK\Authentication\Environment;
use neto737\BitGoSDK\Resources\Wallet\Commands\CommandInterface;
use neto737\BitGoSDK\Resources\Wallet\Address;
use neto737\BitGoSDK\Resources\Wallet\Webhook;

class Client {

    private $auth;
    private $env;

    private $modules;

    private $queryParams;

    private $httpClient;

    public function __construct(Authentication $auth, Environment $env) {
        $this->auth = $auth;

        $this->env = $env;

        $this->prepareClient();
    }

    private function prepareClient(): Client {
        $this->httpClient = new \GuzzleHttp\Client([
            'timeout' => 10,
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => "Bearer {$this->auth->getApiKey()}"
            ],
            'verify' => false
        ]);

        return $this;
    }

    private function buildUri(CommandInterface $command): string {
        $this->queryParams = '?' . http_build_query($command->getQueryParameters());

        return strtr($this->env->getEnvironment() . $command->getEndpoint() . $this->queryParams, [
            '{walletId}'    => $this->env->getWalletId(),
            '{coin}'        => $this->env->getCurrency(),
            '{addressId}'   => $command->getPathParam(),
            '{addressOrId}' => $command->getPathParam(),
            '{webhookId}'   => $command->getPathParam()
        ]);
    }

    public function send(CommandInterface $command) {
        try {
            $response = $this->httpClient->request($command->getRequestMethod(), $this->buildUri($command), [
                RequestOptions::JSON => array_filter($command->getBodyParameters()) ?: null,

                'on_stats' => function (TransferStats $stats) {
                    echo 'Effective URI: ' . $stats->getEffectiveUri() . PHP_EOL;
                    echo 'Transfer Time: ' . $stats->getTransferTime() . PHP_EOL . PHP_EOL;
                    // var_dump($stats->getHandlerStats());
                }
            ]);

            return $command->process($response);
        } catch (ClientException $e) {
            return $e->getMessage();
        }
    }

    private function loadModule($name) {
        if (!isset($this->modules[$name])) {
            $this->modules[$name] = new $name($this);
        }

        return $this->modules[$name];
    }

    public function Address(): Address {
        return $this->loadModule(Address::class);
    }

    public function Webhook(): Webhook {
        return $this->loadModule(Webhook::class);
    }
}
