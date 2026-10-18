<?php

namespace neto737\BitGoSDK\Tests\Fixtures;

use neto737\BitGoSDK\BitGoSDK;

/**
 * A BitGoSDK whose user-webhook methods record the call instead of sending it.
 */
final class RecordingWebhookSdk extends BitGoSDK
{
    /** @var array{string, list<mixed>}|array{} */
    public array $forwarded = [];

    public function __construct()
    {
        parent::__construct('token', 'tbtc', true);
    }

    /**
     * @return array{forwarded: true}
     */
    public function listUserWebhooks()
    {
        $this->forwarded = [__FUNCTION__, func_get_args()];
        return ['forwarded' => true];
    }

    /**
     * @return array{forwarded: true}
     */
    public function addUserWebhook(string $url, string $type, ?string $label = null, ?int $numConfirmations = null)
    {
        $this->forwarded = [__FUNCTION__, func_get_args()];
        return ['forwarded' => true];
    }

    /**
     * @return array{forwarded: true}
     */
    public function removeUserWebhook(string $url, string $type)
    {
        $this->forwarded = [__FUNCTION__, func_get_args()];
        return ['forwarded' => true];
    }
}
