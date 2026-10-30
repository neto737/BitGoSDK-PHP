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

declare(strict_types=1);

namespace neto737\BitGoSDK\Internal;

use neto737\BitGoSDK\Exception\InvalidArgumentException;

/**
 * Builds URL paths from caller-supplied values.
 *
 * @internal Not part of the public API. It may change or go away in any release.
 */
final class Path
{
    /**
     * Fill each {name} in $template with its value from $params, escaped as one path segment.
     *
     * @param array<string, string> $params
     * @throws InvalidArgumentException if a value is empty, '.' or '..'
     */
    public static function build(string $template, array $params): string
    {
        return (string) preg_replace_callback('/\{(\w+)\}/', static function (array $match) use ($template, $params): string {
            if (!isset($params[$match[1]])) {
                throw new \LogicException('No value for {' . $match[1] . '} in ' . $template);
            }
            return self::segment($params[$match[1]]);
        }, $template);
    }

    /**
     * Percent-encode $value so it stays one path segment: '/', '?', '#' and '%' can't leave the endpoint or add a query.
     *
     * XRP and XLM addresses with a tag or memo ('r…?dt=1', 'G…?memoId=1') are escaped whole too. BitGo resolves the
     * escaped form to the tagged address.
     *
     * @throws InvalidArgumentException if $value is empty, '.' or '..'
     */
    public static function segment(string $value): string
    {
        self::check($value);
        return rawurlencode($value);
    }

    /**
     * @throws InvalidArgumentException if $value is empty, '.' or '..', which would point the request at a different endpoint
     */
    public static function check(string $value): void
    {
        if (in_array($value, ['', '.', '..'], true)) {
            throw new InvalidArgumentException('Invalid ID in the request path: ' . var_export($value, true));
        }
    }
}
