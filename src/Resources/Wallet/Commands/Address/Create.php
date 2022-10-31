<?php

namespace neto737\BitGoSDK\Resources\Wallet\Commands\Address;

use neto737\BitGoSDK\Resources\Wallet\Commands\Command;

class Create extends Command
{
    private $chain;
    private $label;
    private $lowPriority;
    private $gasPrice;
    private $forwarderVersion;
    private $onToken;
    private $format;

    public function __construct(?int $chain = null, ?string $label = null, ?bool $lowPriority = null, int $gasPrice = null, ?int $forwarderVersion = null, ?string $onToken = null, ?string $format = null)
    {
        $this->chain            = $chain;
        $this->label            = $label;
        $this->lowPriority      = $lowPriority;
        $this->gasPrice         = $gasPrice;
        $this->forwarderVersion = $forwarderVersion;
        $this->onToken          = $onToken;
        $this->format           = $format;
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    public function getEndpoint(): string
    {
        return '{coin}/wallet/{walletId}/address';
    }

    public function getBodyParameters(): array
    {
        return [
            'chain'             => $this->chain,
            'label'             => $this->label,
            'lowPriority'       => $this->lowPriority,
            'gasPrice'          => $this->gasPrice,
            'forwarderVersion'  => $this->forwarderVersion,
            'onToken'           => $this->onToken,
            'format'            => $this->format
        ];
    }
}
