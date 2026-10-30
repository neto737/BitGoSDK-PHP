<?php

declare(strict_types=1);

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\Exception\ApiException;
use neto737\BitGoSDK\Exception\InvalidArgumentException;
use neto737\BitGoSDK\Exception\NotFoundException;
use neto737\BitGoSDK\Wallet\Webhooks;

final class WebhooksTest extends TestCase
{
    private const string URL = self::API . 'tbtc/wallet/w1/webhooks';
    private const string HOOK = 'https://example.com/bitgo';

    private function webhooks(): Webhooks
    {
        return $this->bitgo()->coin('tbtc')->wallet('w1')->webhooks();
    }

    public function testAddSendsOnlyTypeAndUrlByDefault(): void
    {
        $this->respond(['id' => 'h1', 'coin' => 'tbtc', 'url' => self::HOOK, 'type' => 'transfer']);

        self::assertSame(['id' => 'h1', 'coin' => 'tbtc', 'url' => self::HOOK, 'type' => 'transfer'], $this->webhooks()->add('transfer', self::HOOK));
        self::assertRequest($this->lastRequest(), 'POST', self::URL, ['type' => 'transfer', 'url' => self::HOOK]);
    }

    public function testAddSendsEveryNamedParam(): void
    {
        $this->webhooks()->add(
            'txRequest',
            self::HOOK,
            label: 'Ops',
            numConfirmations: 0,
            allToken: true,
            listenToFailureStates: false,
            txRequestStates: ['pendingApproval', 'delivered'],
            txRequestTransactionStates: ['signed'],
            customHttpHeaders: ['X-Secret' => 's3cret'],
            extra: ['label' => 'overridden', 'newParam' => 1],
        );

        self::assertRequest($this->lastRequest(), 'POST', self::URL, [
            'label' => 'Ops',
            'newParam' => 1,
            'type' => 'txRequest',
            'url' => self::HOOK,
            'numConfirmations' => 0,
            'allToken' => true,
            'listenToFailureStates' => false,
            'txRequestStates' => ['pendingApproval', 'delivered'],
            'txRequestTransactionStates' => ['signed'],
            'customHttpHeaders' => ['X-Secret' => 's3cret'],
        ]);
    }

    public function testEmptyCustomHttpHeadersAreSentAsAnObject(): void
    {
        $this->webhooks()->add('transfer', self::HOOK, customHttpHeaders: []);

        self::assertStringContainsString('"customHttpHeaders":{}', (string) $this->lastRequest()->getBody());
    }

    public function testAddThatBitGoRejectsThrows(): void
    {
        $this->respond(['error' => 'invalid url', 'name' => 'InvalidUrl', 'requestId' => 'req-1'], 400);

        try {
            $this->webhooks()->add('transfer', 'not a url');
            self::fail('No exception was thrown.');
        } catch (ApiException $e) {
            self::assertSame(400, $e->getStatusCode());
            self::assertSame('InvalidUrl', $e->getErrorName());
        }
    }

    public function testListSendsNothingUntilIteratedAndYieldsTheWebhooks(): void
    {
        $this->respond(['webhooks' => [['id' => 'h1'], ['id' => 'h2']]]);

        $list = $this->webhooks()->list(extra: ['type' => 'transfer']);

        self::assertSame([], $this->requests(), 'list() sent a request before iterating.');
        self::assertSame([['id' => 'h1'], ['id' => 'h2']], iterator_to_array($list));
        self::assertRequest($this->lastRequest(), 'GET', self::URL . '?type=transfer', null);
    }

    public function testListFollowsNextBatchPrevId(): void
    {
        $this->respond(['webhooks' => [['id' => 'h1']], 'nextBatchPrevId' => 'h1']);
        $this->respond(['webhooks' => [['id' => 'h2']]]);

        self::assertSame([['id' => 'h1'], ['id' => 'h2']], iterator_to_array($this->webhooks()->list()));
        self::assertCount(2, $this->requests());
        self::assertRequest($this->requests()[1], 'GET', self::URL . '?prevId=h1', null);
    }

    public function testRemoveById(): void
    {
        $this->respond(['removed' => 1]);

        self::assertSame(['removed' => 1], $this->webhooks()->remove(id: 'h1'));
        self::assertRequest($this->lastRequest(), 'DELETE', self::URL, ['id' => 'h1']);
    }

    public function testRemoveByTypeAndUrl(): void
    {
        $this->webhooks()->remove('transfer', self::HOOK);

        self::assertRequest($this->lastRequest(), 'DELETE', self::URL, ['type' => 'transfer', 'url' => self::HOOK]);
    }

    public function testRemoveWithNoParamsIsRejectedBeforeAnyRequest(): void
    {
        try {
            $this->webhooks()->remove();
            self::fail('No exception was thrown.');
        } catch (InvalidArgumentException) {
            self::assertSame([], $this->requests());
        }
    }

    public function testSimulateSendsEveryNamedParam(): void
    {
        $this->respond(['webhookNotifications' => [['id' => 'n1', 'simulation' => true]]]);

        $simulated = $this->webhooks()->simulate('h1', transferId: 't1', pendingApprovalId: 'p1', txRequestId: 'r1', txHash: '0xabc', usePlaceholderData: false, extra: ['newParam' => 1]);

        self::assertSame(['webhookNotifications' => [['id' => 'n1', 'simulation' => true]]], $simulated);
        self::assertRequest($this->lastRequest(), 'POST', self::URL . '/h1/simulate', [
            'newParam' => 1,
            'transferId' => 't1',
            'pendingApprovalId' => 'p1',
            'txRequestId' => 'r1',
            'txHash' => '0xabc',
            'usePlaceholderData' => false,
        ]);
    }

    public function testSimulateEscapesTheWebhookId(): void
    {
        $this->webhooks()->simulate('../h1', usePlaceholderData: true);

        self::assertRequest($this->lastRequest(), 'POST', self::URL . '/..%2Fh1/simulate', ['usePlaceholderData' => true]);
    }

    public function testSimulateWithAnEmptyWebhookIdIsRejectedBeforeAnyRequest(): void
    {
        try {
            $this->webhooks()->simulate('');
            self::fail('No exception was thrown.');
        } catch (InvalidArgumentException) {
            self::assertSame([], $this->requests());
        }
    }

    public function testSimulateAnUnknownWebhookThrowsNotFound(): void
    {
        $this->respond(['error' => 'webhook not found', 'name' => 'WebhookNotFound'], 404);

        $this->expectException(NotFoundException::class);
        $this->webhooks()->simulate('h404', usePlaceholderData: true);
    }
}
