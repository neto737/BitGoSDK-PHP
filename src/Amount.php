<?php

/**
 *  ____  _ _    ____      ____  ____  _  __
 * | __ )(_) |_ / ___| ___/ ___||  _ \| |/ /
 * |  _ \| | __| |  _ / _ \___ \| | | | ' /
 * | |_) | | |_| |_| | (_) |__) | |_| | . \
 * |____/|_|\__|\____|\___/____/|____/|_|\_\
 *
 * @package BitGoSDK PHP
 * @author  Neto Melo <neto737@live.com>
 * @license https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace neto737\BitGoSDK;

use neto737\BitGoSDK\Exception\InvalidArgumentException;

/**
 * An exact amount of a coin, kept as a string of base units (satoshis, wei…) so it never goes through a float.
 *
 * BitGo sends and expects amounts in base units as strings, and responses keep them that way. Use this when you
 * need to convert to or from what people read: 10 ETH is '10000000000000000000' wei, more than an int can hold.
 * Anywhere a request takes an amount, an Amount is sent as its base units.
 *
 * ```php
 * Amount::fromDisplay('0.5', decimals: 8)->baseUnits;                   // '50000000'
 * Amount::fromBaseUnits($transfer['valueString'], decimals: 8)->toDisplay();   // '-0.0012'
 * ```
 */
final readonly class Amount implements \JsonSerializable, \Stringable
{
    /**
     * @param string $baseUnits A whole number of base units, for example '50000000'
     * @param int $decimals     How many decimal places the coin has, for example 8 for BTC or 18 for ETH
     */
    private function __construct(public string $baseUnits, public int $decimals)
    {
    }

    /**
     * An amount in base units, such as a BitGo response's 'valueString' or 'balanceString'.
     *
     * @param string|int $baseUnits A whole number, for example '50000000'. It may be negative.
     * @param int $decimals         How many decimal places the coin has, for example 8 for BTC or 18 for ETH
     * @throws InvalidArgumentException if $baseUnits isn't a whole number or $decimals is negative
     */
    public static function fromBaseUnits(string|int $baseUnits, int $decimals): self
    {
        self::checkDecimals($decimals);
        if (!preg_match('/^(-?)(\d+)$/', (string) $baseUnits, $matches)) {
            throw new InvalidArgumentException('Base units must be a whole number, got ' . var_export($baseUnits, true) . '. Use fromDisplay() for a decimal amount.');
        }
        return new self(self::normalize($matches[1], $matches[2]), $decimals);
    }

    /**
     * An amount as people write it, for example '0.5' BTC.
     *
     * @param string $display   A decimal number, for example '0.5' or '-12.25'
     * @param int $decimals     How many decimal places the coin has, for example 8 for BTC or 18 for ETH
     * @throws InvalidArgumentException if $display isn't a decimal number, has more decimal places than the coin, or $decimals is negative
     */
    public static function fromDisplay(string $display, int $decimals): self
    {
        self::checkDecimals($decimals);
        if (!preg_match('/^(-?)(\d*)(?:\.(\d*))?$/', $display, $matches) || $matches[2] . ($matches[3] ?? '') === '') {
            throw new InvalidArgumentException('The amount ' . var_export($display, true) . ' isn\'t a decimal number.');
        }
        $fraction = rtrim($matches[3] ?? '', '0');
        if (strlen($fraction) > $decimals) {
            throw new InvalidArgumentException('The amount ' . $display . ' has more than ' . $decimals . ' decimal places, so it can\'t be sent exactly.');
        }
        return new self(self::normalize($matches[1], $matches[2] . str_pad($fraction, $decimals, '0')), $decimals);
    }

    /**
     * The amount as people read it, without trailing zeros: '0.5', '10' or '-0.0012'.
     */
    public function toDisplay(): string
    {
        $negative = str_starts_with($this->baseUnits, '-');
        $digits = str_pad(ltrim($this->baseUnits, '-'), $this->decimals + 1, '0', STR_PAD_LEFT);
        $whole = substr($digits, 0, strlen($digits) - $this->decimals);
        $fraction = rtrim(substr($digits, strlen($whole)), '0');

        return ($negative ? '-' : '') . $whole . ($fraction === '' ? '' : '.' . $fraction);
    }

    /**
     * The base units, which is what BitGo expects in a query string.
     */
    public function __toString(): string
    {
        return $this->baseUnits;
    }

    /**
     * The base units as a JSON string, which is what BitGo expects in a request body.
     */
    public function jsonSerialize(): string
    {
        return $this->baseUnits;
    }

    /**
     * @throws InvalidArgumentException
     */
    private static function checkDecimals(int $decimals): void
    {
        if ($decimals < 0) {
            throw new InvalidArgumentException('Decimals can\'t be negative, got ' . $decimals . '.');
        }
    }

    /**
     * Base units without leading zeros, and with no sign on zero.
     */
    private static function normalize(string $sign, string $digits): string
    {
        $digits = ltrim($digits, '0');
        return $digits === '' ? '0' : $sign . $digits;
    }
}
