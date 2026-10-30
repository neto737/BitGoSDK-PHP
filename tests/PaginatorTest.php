<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\Paginator;

final class PaginatorTest extends TestCase
{
    private const string URL = self::API . 'tbtc/wallet/w1/addresses';

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $extra
     */
    private function paginator(array $query = [], array $extra = []): Paginator
    {
        return new Paginator($this->http(), '{coin}/wallet/{walletId}/addresses', ['coin' => 'tbtc', 'walletId' => 'w1'], $query, $extra, 'addresses');
    }

    public function testNothingIsSentUntilYouIterate(): void
    {
        $this->paginator();

        self::assertSame([], $this->requests());
    }

    public function testFollowsNextBatchPrevIdUntilTheLastBatch(): void
    {
        $this->respond(['addresses' => [['id' => '1'], ['id' => '2']], 'nextBatchPrevId' => 'p2']);
        $this->respond(['addresses' => [['id' => '3']], 'nextBatchPrevId' => 'p3']);
        $this->respond(['addresses' => [['id' => '4']]]);

        $items = iterator_to_array($this->paginator(['limit' => 2]));

        self::assertSame([['id' => '1'], ['id' => '2'], ['id' => '3'], ['id' => '4']], $items);
        self::assertSame(
            [self::URL . '?limit=2', self::URL . '?limit=2&prevId=p2', self::URL . '?limit=2&prevId=p3'],
            array_map(static fn (\Psr\Http\Message\RequestInterface $request): string => (string) $request->getUri(), $this->requests())
        );
    }

    public function testEmptyNextBatchPrevIdEndsTheList(): void
    {
        $this->respond(['addresses' => [['id' => '1']], 'nextBatchPrevId' => '']);

        self::assertCount(1, iterator_to_array($this->paginator()));
        self::assertCount(1, $this->requests());
    }

    public function testBatchWithoutItemsYieldsNothing(): void
    {
        $this->respond([]);

        self::assertSame([], iterator_to_array($this->paginator()));
    }

    public function testIteratingAgainStartsOver(): void
    {
        $this->respond(['addresses' => [['id' => '1']], 'nextBatchPrevId' => 'p2']);
        $this->respond(['addresses' => [['id' => '2']]]);
        $this->respond(['addresses' => [['id' => '1']]]);
        $paginator = $this->paginator();

        iterator_to_array($paginator);
        iterator_to_array($paginator);

        self::assertSame(self::URL, (string) $this->requests()[2]->getUri());
    }

    public function testPrevIdInExtraStartsFromThere(): void
    {
        $this->respond(['addresses' => [['id' => '5']], 'nextBatchPrevId' => 'p6']);
        $this->respond(['addresses' => [['id' => '6']]]);

        iterator_to_array($this->paginator([], ['prevId' => 'p5']));

        self::assertSame(
            [self::URL . '?prevId=p5', self::URL . '?prevId=p6'],
            array_map(static fn (\Psr\Http\Message\RequestInterface $request): string => (string) $request->getUri(), $this->requests())
        );
    }
}
