<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;

/**
 * B4: policy-rule methods must put the wallet ID, not the rule ID, in the URL.
 *
 * The client points at a closed local port, so each call fails fast and the URL it built can be inspected.
 */
final class PolicyRuleTest extends TestCase
{
    use AccessesPrivateMembers;

    private const UNREACHABLE_ENDPOINT = 'http://127.0.0.1:1/api/v2/tbtc';

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
        $this->setPrivate($sdk, 'APIEndpoint', self::UNREACHABLE_ENDPOINT);
        $sdk->walletId = 'wallet-id';

        $sdk->$method('rule-id', 'velocityLimit', '{}', 'deny');

        self::assertSame(self::UNREACHABLE_ENDPOINT . '/wallet/wallet-id/policy/rule', $this->getPrivate($sdk, 'url'));
    }
}
