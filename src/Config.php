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

namespace neto737\BitGoSDK;

use neto737\BitGoSDK\Exception\InvalidArgumentException;

/**
 * Which BitGo environment to call, and with which access token.
 */
final readonly class Config
{
    public const PRODUCTION_URL = 'https://app.bitgo.com/api/v2/';
    public const TESTNET_URL = 'https://app.bitgo-test.com/api/v2/';

    /** The API base URL, always ending in '/'. */
    public string $baseUrl;

    /**
     * @param string $accessToken  A BitGo access token (API key)
     * @param string $baseUrl      The API base URL. Use production() or testnet() unless you go through a proxy.
     * @throws InvalidArgumentException if the access token is empty or the base URL isn't an https:// URL
     */
    public function __construct(#[\SensitiveParameter] public string $accessToken, string $baseUrl = self::PRODUCTION_URL)
    {
        if ($accessToken === '') {
            throw new InvalidArgumentException('The access token is empty.');
        }
        // The access token travels in a header, so never send it over plain HTTP.
        if (filter_var($baseUrl, FILTER_VALIDATE_URL) === false || !str_starts_with(strtolower($baseUrl), 'https://')) {
            throw new InvalidArgumentException('The base URL must be an https:// URL: ' . $baseUrl);
        }
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    public static function production(#[\SensitiveParameter] string $accessToken): self
    {
        return new self($accessToken, self::PRODUCTION_URL);
    }

    public static function testnet(#[\SensitiveParameter] string $accessToken): self
    {
        return new self($accessToken, self::TESTNET_URL);
    }

    /**
     * Keeps the access token out of var_dump() and print_r() output.
     *
     * @return array{accessToken: string, baseUrl: string}
     */
    public function __debugInfo(): array
    {
        return ['accessToken' => '(hidden)', 'baseUrl' => $this->baseUrl];
    }
}
