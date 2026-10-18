<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use neto737\BitGoSDK\Internal\CurlTransport;
use PHPUnit\Framework\TestCase;

/**
 * The real cURL transport, exercised without reaching the network.
 */
final class CurlTransportTest extends TestCase
{
    use AccessesPrivateMembers;

    public function testReturnsTheBody(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'bitgo');
        self::assertIsString($file);
        file_put_contents($file, '{"id":"w1"}');

        try {
            $response = (new CurlTransport())->send([
                CURLOPT_URL => 'file://' . str_replace('\\', '/', (string) realpath($file)),
                CURLOPT_RETURNTRANSFER => true,
            ]);
        } finally {
            unlink($file);
        }

        self::assertSame('{"id":"w1"}', $response['body']);
        self::assertSame(0, $response['errno']);
        self::assertSame('', $response['error']);
    }

    public function testReportsCurlErrors(): void
    {
        $response = (new CurlTransport())->send([
            CURLOPT_URL => 'nosuchscheme://example.com/',
            CURLOPT_RETURNTRANSFER => true,
        ]);

        self::assertSame('', $response['body']);
        self::assertSame(0, $response['status']);
        self::assertSame(CURLE_UNSUPPORTED_PROTOCOL, $response['errno']);
        self::assertNotSame('', $response['error']);
    }

    /**
     * @dataProvider clients
     * @param BitGoSDK|BitGoExpress $client
     * @param callable(mixed): mixed $request
     */
    public function testClientsSendThroughCurlByDefault($client, callable $request): void
    {
        // cURL rejects the scheme before anything leaves the machine.
        $this->setPrivate($client, 'APIEndpoint', 'nosuchscheme://example.com/api/v2/tbtc');

        $result = (array) $request($client);

        self::assertInstanceOf(CurlTransport::class, $this->getPrivate($client, 'transport'));
        self::assertSame(0, $result['status']);
        self::assertIsString($result['error']);
        self::assertNotSame('', $result['error']);
    }

    /**
     * @return array<string, array{BitGoSDK|BitGoExpress, callable(mixed): mixed}>
     */
    public static function clients(): array
    {
        return [
            'BitGoSDK' => [new BitGoSDK('token', 'tbtc', true), static fn (BitGoSDK $c) => $c->getWallet()],
            'BitGoExpress' => [new BitGoExpress('localhost', 3080, 'tbtc'), static fn (BitGoExpress $c) => $c->buildTransaction([])],
        ];
    }
}
