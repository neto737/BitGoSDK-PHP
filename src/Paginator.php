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

use neto737\BitGoSDK\Exception\BitGoException;
use neto737\BitGoSDK\Internal\Http;

/**
 * The items of a list endpoint, fetched one batch at a time as you iterate.
 *
 * Nothing is sent until you iterate. Each batch is one request, which passes the previous batch's nextBatchPrevId as
 * prevId, and iterating again starts over from the first batch.
 *
 * @implements \IteratorAggregate<int, array<string, mixed>>
 */
final class Paginator implements \IteratorAggregate
{
    /**
     * @internal Returned by list methods, such as Addresses::list().
     * @param array<string, string> $path
     * @param array<string, mixed> $query
     * @param array<string, mixed> $extra
     * @param string $itemsKey    The key that holds the items in each batch, for example 'addresses'
     */
    public function __construct(
        private readonly Http $http,
        private readonly string $template,
        private readonly array $path,
        private readonly array $query,
        private readonly array $extra,
        private readonly string $itemsKey,
    ) {
    }

    /**
     * @return \Generator<int, array<string, mixed>>
     * @throws BitGoException
     */
    public function getIterator(): \Generator
    {
        $query = $this->query;
        do {
            $batch = $this->http->getBatch($this->template, $this->path, $query, $this->extra, $this->itemsKey);
            // Not 'yield from', which would repeat each batch's keys (0, 1, …) and break iterator_to_array().
            foreach ($batch['items'] as $item) {
                yield $item;
            }
            $query['prevId'] = $batch['nextBatchPrevId'];
        } while ($query['prevId'] !== null);
    }
}
