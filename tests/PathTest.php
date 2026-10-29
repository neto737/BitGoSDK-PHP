<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\Exception\BitGoException;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use neto737\BitGoSDK\Internal\Path;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Caller-supplied values stay one path segment, so they can't point a request at a different endpoint.
 */
final class PathTest extends TestCase
{
    public function testBuildFillsEachPlaceholder(): void
    {
        self::assertSame('tbtc/wallet/w1/address/a1', Path::build('{coin}/wallet/{walletId}/address/{address}', [
            'coin' => 'tbtc',
            'walletId' => 'w1',
            'address' => 'a1',
        ]));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function escapes(): array
    {
        return [
            'slash and dots' => ['../../user/me', '..%2F..%2Fuser%2Fme'],
            'query' => ['w1?x=1', 'w1%3Fx%3D1'],
            'fragment' => ['w1#x', 'w1%23x'],
            'percent' => ['w1%2F', 'w1%252F'],
            'XRP tag' => ['rLHzPsX6oXkzU2qL12kHCH8G8cnZv1rBJh?dt=123', 'rLHzPsX6oXkzU2qL12kHCH8G8cnZv1rBJh%3Fdt%3D123'],
            'XLM memo' => ['GAJ7Q5Q5Z2NJXQ6V?memoId=7', 'GAJ7Q5Q5Z2NJXQ6V%3FmemoId%3D7'],
            'plain' => ['2N8hwP1WmJrFF5QWABn38y63uYLhnJYJYTF', '2N8hwP1WmJrFF5QWABn38y63uYLhnJYJYTF'],
        ];
    }

    #[DataProvider('escapes')]
    public function testValuesAreEscapedAsOneSegment(string $value, string $expected): void
    {
        self::assertSame('tbtc/wallet/' . $expected, Path::build('tbtc/wallet/{walletId}', ['walletId' => $value]));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function badSegments(): array
    {
        return ['empty' => [''], 'dot' => ['.'], 'dot dot' => ['..']];
    }

    #[DataProvider('badSegments')]
    public function testEmptyAndDotSegmentsAreRejected(string $value): void
    {
        try {
            Path::build('tbtc/wallet/{walletId}', ['walletId' => $value]);
            self::fail('No exception was thrown.');
        } catch (InvalidArgumentException $e) {
            self::assertInstanceOf(BitGoException::class, $e);
        }
    }

    public function testMissingPlaceholderValueIsABug(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('{walletId}');
        Path::build('{coin}/wallet/{walletId}', ['coin' => 'tbtc']);
    }
}
