<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use Http\Discovery\ClassDiscovery;
use Http\Discovery\Strategy\CommonPsr17ClassesStrategy;
use Http\Discovery\Strategy\MockClientStrategy;
use neto737\BitGoSDK\BitGo;
use neto737\BitGoSDK\Config;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Navigation never sends a request, and verbs send exactly one.
 */
final class BitGoTest extends TestCase
{
    /** @var array<string> */
    private array $strategies;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategies = [...ClassDiscovery::getStrategies()];
    }

    protected function tearDown(): void
    {
        ClassDiscovery::setStrategies($this->strategies);
    }

    public function testNavigationSendsNoRequest(): void
    {
        $coin = $this->bitgo()->coin('tbtc');
        $wallet = $coin->wallet('w1');
        $lists = [$coin->wallets()->list(), $wallet->addresses()->list(), $wallet->webhooks()->list(), $coin->blockWebhooks()->list()];

        self::assertSame('tbtc', $coin->code);
        self::assertSame('tbtc', $wallet->coin);
        self::assertSame('w1', $wallet->id);
        self::assertSame([], $this->requests());
        foreach ($lists as $list) {
            iterator_to_array($list);
        }
        self::assertCount(4, $this->requests());
    }

    public function testWalletGetFetchesEveryTime(): void
    {
        $this->respond(['id' => 'w1', 'coin' => 'tbtc', 'label' => 'Hot', 'balanceString' => '100']);
        $this->respond(['id' => 'w1', 'coin' => 'tbtc', 'label' => 'Hot', 'balanceString' => '250']);
        $wallet = $this->bitgo()->coin('tbtc')->wallet('w1');

        self::assertSame('100', $wallet->get()['balanceString'] ?? null);
        self::assertSame('250', $wallet->get(allTokens: true)['balanceString'] ?? null);
        self::assertCount(2, $this->requests());
        self::assertRequest($this->requests()[0], 'GET', self::API . 'tbtc/wallet/w1', null);
        self::assertRequest($this->requests()[1], 'GET', self::API . 'tbtc/wallet/w1?allTokens=true', null);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function badIds(): array
    {
        return ['empty' => [''], 'dot' => ['.'], 'dot dot' => ['..']];
    }

    #[DataProvider('badIds')]
    public function testBadCoinIsRejectedBeforeAnyRequest(string $coin): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->bitgo()->coin($coin);
    }

    #[DataProvider('badIds')]
    public function testBadWalletIdIsRejectedBeforeAnyRequest(string $id): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->bitgo()->coin('tbtc')->wallet($id);
    }

    public function testFindsAnInstalledClientWhenNoneIsPassed(): void
    {
        ClassDiscovery::setStrategies([MockClientStrategy::class, CommonPsr17ClassesStrategy::class]);

        $bitgo = new BitGo(Config::testnet(self::TOKEN));
        $bitgo->coin('tbtc')->wallet('w1')->get();

        $this->addToAssertionCount(1);
    }

    public function testExplainsWhatToInstallWhenNoClientIsFound(): void
    {
        ClassDiscovery::setStrategies([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('composer require guzzlehttp/guzzle');
        new BitGo(Config::testnet(self::TOKEN));
    }
}
