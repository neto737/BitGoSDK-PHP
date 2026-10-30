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

use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use neto737\BitGoSDK\Internal\Http;
use neto737\BitGoSDK\Internal\Path;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * The BitGo API client.
 *
 * Methods named after a thing (coin(), wallet(), addresses()) only navigate and never send a request. Methods named
 * after an action (get(), create(), list()…) send one each time you call them.
 *
 * ```php
 * $bitgo = new BitGo(Config::testnet($accessToken));
 * $address = $bitgo->coin('tbtc')->wallet($walletId)->addresses()->create(label: 'Deposit 42');
 * ```
 */
final readonly class BitGo
{
    // Sent in the User-Agent header. Bump it with every release.
    public const string VERSION = '3.0.0-alpha.1';

    private Http $http;

    /**
     * Any PSR-18 HTTP client works. Timeouts, proxies and TLS settings belong to the client, so configure them there.
     * Leave the client and factories out to use the ones installed in your project, such as Guzzle or Symfony HttpClient.
     *
     * @throws InvalidArgumentException if no client or factory was passed and none is installed
     */
    public function __construct(
        Config $config,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        try {
            $this->http = new Http(
                $config,
                $httpClient ?? Psr18ClientDiscovery::find(),
                $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory(),
                $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory(),
            );
        } catch (NotFoundException $e) {
            throw new InvalidArgumentException(
                'No PSR-18 HTTP client or PSR-17 factory was passed or found. Install one, for example with '
                . '"composer require guzzlehttp/guzzle", or pass your own to the BitGo constructor.',
                0,
                $e,
            );
        }
    }

    /**
     * A coin, by its BitGo code: CoinCode::TBTC, or any code as a string, for example 'tbtc' or 'ttao'. Sends no
     * request.
     *
     * @throws InvalidArgumentException if $coin is empty, '.' or '..'
     */
    public function coin(CoinCode|string $coin): Coin
    {
        $coin = $coin instanceof CoinCode ? $coin->value : $coin;
        Path::check($coin);
        return new Coin($this->http, $coin);
    }

    /**
     * The HTTP response to the latest request this client sent, for its status and headers. Verbs return only the
     * decoded body, so read this right after the call.
     *
     * It is shared by every coin and wallet you got from this client. After an error it is the error response, the
     * same one as ApiException::getResponse(). While you iterate a list(), it is the latest batch fetched.
     *
     * ```php
     * $address = $wallet->addresses()->create(label: 'Deposit 42');
     * $date = $bitgo->lastResponse()?->getHeaderLine('Date');
     * ```
     *
     * @return ResponseInterface|null null before the first request, and after a request that got no response, such as a network error
     */
    public function lastResponse(): ?ResponseInterface
    {
        return $this->http->lastResponse();
    }
}
