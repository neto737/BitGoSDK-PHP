<?php

/**
 *  ____  _ _    ____      ____  ____  _  __
 * | __ )(_) |_ / ___| ___/ ___||  _ \| |/ /
 * |  _ \| | __| |  _ / _ \___ \| | | | ' /
 * | |_) | | |_| |_| | (_) |__) | |_| | . \
 * |____/|_|\__|\____|\___/____/|____/|_|\_\
 *
 * @package BitGoSDK PHP
 * @author  Neto Melo <neto737@live.com>
 * @license https://opensource.org/licenses/MIT MIT License
 */

namespace neto737\BitGoSDK\Internal;

/**
 * Sends requests with ext-curl.
 *
 * @internal Not part of the public API. It may change or go away in any release.
 */
final class CurlTransport implements Transport
{
    public function send(array $options): array
    {
        $ch = curl_init();
        if ($ch === false) {
            return ['body' => '', 'status' => 0, 'errno' => CURLE_FAILED_INIT, 'error' => 'Failed to initialise cURL'];
        }

        curl_setopt_array($ch, $options);
        $body = curl_exec($ch);

        return [
            'body' => is_string($body) ? $body : '',
            'status' => (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE),
            'errno' => curl_errno($ch),
            'error' => curl_error($ch),
        ];
    }
}
