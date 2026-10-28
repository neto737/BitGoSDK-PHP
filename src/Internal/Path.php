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
 * Builds URL path segments from caller-supplied values.
 *
 * @internal Not part of the public API. It may change or go away in any release.
 */
final class Path
{
    /**
     * Percent-encode $value so it stays one path segment: '/', '?', '#' and '%' can't leave the endpoint or add a query.
     *
     * @throws \InvalidArgumentException if $value is empty, '.' or '..', which would point the request at a different endpoint
     */
    public static function segment(string $value): string
    {
        if ($value === '' || $value === '.' || $value === '..') {
            throw new \InvalidArgumentException('Invalid ID in the request path: ' . var_export($value, true));
        }
        return rawurlencode($value);
    }
}
