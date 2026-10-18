<?php

namespace neto737\BitGoSDK\Tests;

use ReflectionMethod;
use ReflectionProperty;

/**
 * Reflection helpers for reaching client internals until requests go through a capturable transport.
 */
trait AccessesPrivateMembers
{
    /**
     * @param object|class-string $client
     * @param mixed ...$args
     * @return mixed
     */
    private function invoke($client, string $name, ...$args)
    {
        $method = new ReflectionMethod($client, $name);
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }
        return $method->invoke(is_object($client) ? $client : null, ...$args);
    }

    /**
     * @param object $client
     * @param mixed $value
     */
    private function setPrivate($client, string $name, $value): void
    {
        $property = new ReflectionProperty($client, $name);
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }
        $property->setValue($client, $value);
    }

    /**
     * @param object $client
     * @return mixed
     */
    private function getPrivate($client, string $name)
    {
        $property = new ReflectionProperty($client, $name);
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }
        return $property->getValue($client);
    }
}
