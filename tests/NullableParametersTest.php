<?php

namespace neto737\BitGoSDK\Tests;

use neto737\BitGoSDK\BitGoExpress;
use neto737\BitGoSDK\BitGoSDK;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;

/**
 * B9: a typed parameter that defaults to null must say so (?string $x = null). The implicit form is deprecated in PHP 8.4.
 */
final class NullableParametersTest extends TestCase
{
    /**
     * @return array<string, array{class-string}>
     */
    public function clientProvider(): array
    {
        return [
            'BitGoSDK' => [BitGoSDK::class],
            'BitGoExpress' => [BitGoExpress::class],
        ];
    }

    /**
     * @dataProvider clientProvider
     * @param class-string $class
     */
    public function testNullDefaultsAreExplicitlyNullable(string $class): void
    {
        $implicit = [];
        foreach ((new ReflectionClass($class))->getMethods() as $method) {
            foreach ($method->getParameters() as $parameter) {
                if (!$parameter->isDefaultValueAvailable() || $parameter->getDefaultValue() !== null) {
                    continue;
                }
                $type = $parameter->getType();
                if ($type instanceof ReflectionNamedType && !self::declaresNull($method->getFileName(), $method->getStartLine(), $method->getEndLine(), $parameter->getName())) {
                    $implicit[] = $method->getName() . '($' . $parameter->getName() . ')';
                }
            }
        }

        self::assertSame([], $implicit);
    }

    /**
     * Reflection reports `string $x = null` as nullable, so read the declaration from the source.
     *
     * @param string|false $file
     * @param int|false $startLine
     * @param int|false $endLine
     */
    private static function declaresNull($file, $startLine, $endLine, string $name): bool
    {
        self::assertIsString($file);
        self::assertIsInt($startLine);
        self::assertIsInt($endLine);

        $lines = file($file);
        self::assertIsArray($lines);
        $source = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        return preg_match('/\?\\\\?[A-Za-z_][A-Za-z0-9_\\\\]*\s+\$' . preg_quote($name, '/') . '\s*=\s*null\b/i', $source) === 1;
    }
}
