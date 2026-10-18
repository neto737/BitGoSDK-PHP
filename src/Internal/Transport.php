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
 * Sends one HTTP request described by cURL options. Lets tests capture requests instead of sending them.
 *
 * @internal Not part of the public API. It may change or go away in any release.
 */
interface Transport
{
    /**
     * @param array<int, mixed> $options    cURL options, as passed to curl_setopt_array()
     * @return array{body: string, status: int, errno: int, error: string}
     *                                      The response body and HTTP status, or a non-zero cURL errno and its message
     */
    public function send(array $options): array;
}
