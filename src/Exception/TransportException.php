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

namespace neto737\BitGoSDK\Exception;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * No response arrived: the HTTP client failed, for example on a network error or a timeout.
 *
 * The request may or may not have reached BitGo, so check before you retry anything that moves funds.
 */
class TransportException extends \RuntimeException implements BitGoException
{
    public function __construct(ClientExceptionInterface $previous)
    {
        parent::__construct('The request to BitGo failed: ' . $previous->getMessage(), 0, $previous);
    }
}
