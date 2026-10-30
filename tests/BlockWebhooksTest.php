<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\Coin\BlockWebhooks;
use neto737\BitGoSDK\Exception\ApiException;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use neto737\BitGoSDK\Exception\NotFoundException;

final class BlockWebhooksTest extends TestCase
{
    private const URL = self::API . 'tbtc/webhooks';
    private const HOOK = 'https://example.com/bitgo';

    private function webhooks(): BlockWebhooks
    {
        return $this->bitgo()->coin('tbtc')->blockWebhooks();
    }

    public function testAddSendsOnlyTypeAndUrlByDefault(): void
    {
        $this->respond(['id' => 'h1', 'coin' => 'tbtc', 'url' => self::HOOK, 'type' => 'block']);

        self::assertSame(['id' => 'h1', 'coin' => 'tbtc', 'url' => self::HOOK, 'type' => 'block'], $this->webhooks()->add('block', self::HOOK));
        self::assertRequest($this->lastRequest(), 'POST', self::URL, ['type' => 'block', 'url' => self::HOOK]);
    }

    public function testAddSendsEveryNamedParam(): void
    {
        $this->webhooks()->add('wallet_confirmation', self::HOOK, label: 'Ops', numConfirmations: 3, extra: ['newParam' => 1]);

        self::assertRequest($this->lastRequest(), 'POST', self::URL, [
            'newParam' => 1,
            'type' => 'wallet_confirmation',
            'url' => self::HOOK,
            'label' => 'Ops',
            'numConfirmations' => 3,
        ]);
    }

    public function testAddThatBitGoRejectsThrows(): void
    {
        $this->respond(['error' => 'invalid type', 'name' => 'InvalidType', 'requestId' => 'req-1'], 400);

        try {
            $this->webhooks()->add('transfer', self::HOOK);
            self::fail('No exception was thrown.');
        } catch (ApiException $e) {
            self::assertSame(400, $e->getStatusCode());
            self::assertSame('InvalidType', $e->getErrorName());
        }
    }

    public function testListSendsNothingUntilIteratedAndYieldsTheWebhooks(): void
    {
        $this->respond(['webhooks' => [['id' => 'h1'], ['id' => 'h2']]]);

        $list = $this->webhooks()->list();

        self::assertSame([], $this->requests(), 'list() sent a request before iterating.');
        self::assertSame([['id' => 'h1'], ['id' => 'h2']], iterator_to_array($list));
        self::assertRequest($this->lastRequest(), 'GET', self::URL, null);
    }

    public function testListFollowsNextBatchPrevId(): void
    {
        $this->respond(['webhooks' => [['id' => 'h1']], 'nextBatchPrevId' => 'h1']);
        $this->respond(['webhooks' => [['id' => 'h2']]]);

        self::assertSame([['id' => 'h1'], ['id' => 'h2']], iterator_to_array($this->webhooks()->list()));
        self::assertCount(2, $this->requests());
        self::assertRequest($this->requests()[1], 'GET', self::URL . '?prevId=h1', null);
    }

    public function testRemove(): void
    {
        $this->respond(['removed' => 1]);

        self::assertSame(['removed' => 1], $this->webhooks()->remove('block', self::HOOK, id: 'h1'));
        self::assertRequest($this->lastRequest(), 'DELETE', self::URL, ['type' => 'block', 'url' => self::HOOK, 'id' => 'h1']);
    }

    public function testSimulate(): void
    {
        $this->respond(['webhookNotifications' => [['id' => 'n1', 'simulation' => true]]]);

        self::assertSame(['webhookNotifications' => [['id' => 'n1', 'simulation' => true]]], $this->webhooks()->simulate('h1', blockId: 'b1'));
        self::assertRequest($this->lastRequest(), 'POST', self::URL . '/h1/simulate', ['blockId' => 'b1']);
    }

    public function testSimulateWithABadWebhookIdIsRejectedBeforeAnyRequest(): void
    {
        try {
            $this->webhooks()->simulate('..');
            self::fail('No exception was thrown.');
        } catch (InvalidArgumentException) {
            self::assertSame([], $this->requests());
        }
    }

    public function testSimulateAnUnknownWebhookThrowsNotFound(): void
    {
        $this->respond(['error' => 'webhook not found', 'name' => 'WebhookNotFound'], 404);

        $this->expectException(NotFoundException::class);
        $this->webhooks()->simulate('h404');
    }
}
