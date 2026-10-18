<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Tests\Fixtures\RecordingTransport;
use PHPUnit\Framework\TestCase;

/**
 * B4: policy-rule methods must put the wallet ID, not the rule ID, in the URL.
 */
final class PolicyRuleTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function policyRuleMethods(): array
    {
        return [
            'set' => ['setVelocityPolicyRule'],
            'update' => ['updateVelocityPolicyRule'],
            'remove' => ['removePolicyRule'],
        ];
    }

    /**
     * @dataProvider policyRuleMethods
     */
    public function testUrlUsesWalletId(string $method): void
    {
        $sdk = new BitGoSDK('token', 'tbtc', true);
        $sdk->walletId = 'wallet-id';
        $transport = RecordingTransport::on($sdk);

        $sdk->$method('rule-id', 'velocityLimit', '{}', 'deny');

        self::assertSame('https://app.bitgo-test.com/api/v2/tbtc/wallet/wallet-id/policy/rule', $transport->last()->url);
    }
}
