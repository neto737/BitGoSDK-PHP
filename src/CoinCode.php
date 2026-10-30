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

/**
 * BitGo's codes for the major coins and their testnets, so an IDE can complete them and a typo fails to compile.
 *
 * BitGo supports many more coins than these. BitGo::coin() takes any code as a string too, for example 'tao', so a
 * coin missing here never needs an SDK release. The codes and decimals come from BitGo's public asset list.
 *
 * ```php
 * $bitgo->coin(CoinCode::TBTC)->wallet($walletId)->get();
 * Amount::fromDisplay('0.5', CoinCode::TBTC->decimals())->baseUnits;   // '50000000'
 * ```
 */
enum CoinCode: string
{
    case BTC = 'btc';
    case TBTC = 'tbtc';
    // Bitcoin testnet4. BitGo's docs now use it as the Bitcoin testnet, and tbtc (testnet3) still works.
    case TBTC4 = 'tbtc4';
    case BCH = 'bch';
    case TBCH = 'tbch';
    case LTC = 'ltc';
    case TLTC = 'tltc';
    case DOGE = 'doge';
    case TDOGE = 'tdoge';
    case DASH = 'dash';
    case TDASH = 'tdash';
    case ZEC = 'zec';
    case TZEC = 'tzec';

    case ETH = 'eth';
    // Ethereum on Hoodi.
    case HTETH = 'hteth';
    // Ethereum on Sepolia.
    case SEPETH = 'sepeth';
    case POLYGON = 'polygon';
    case TPOLYGON = 'tpolygon';
    case ARBETH = 'arbeth';
    case TARBETH = 'tarbeth';
    case OPETH = 'opeth';
    case TOPETH = 'topeth';
    case BASEETH = 'baseeth';
    case TBASEETH = 'tbaseeth';
    case BSC = 'bsc';
    case TBSC = 'tbsc';
    case AVAXC = 'avaxc';
    case TAVAXC = 'tavaxc';

    case SOL = 'sol';
    case TSOL = 'tsol';
    case XRP = 'xrp';
    case TXRP = 'txrp';
    case XLM = 'xlm';
    case TXLM = 'txlm';
    case TRX = 'trx';
    case TTRX = 'ttrx';
    case ALGO = 'algo';
    case TALGO = 'talgo';
    case ADA = 'ada';
    case TADA = 'tada';
    case DOT = 'dot';
    case TDOT = 'tdot';
    case ATOM = 'atom';
    case TATOM = 'tatom';
    case NEAR = 'near';
    case TNEAR = 'tnear';
    case SUI = 'sui';
    case TSUI = 'tsui';
    case APT = 'apt';
    case TAPT = 'tapt';
    case TON = 'ton';
    case TTON = 'tton';
    case HBAR = 'hbar';
    case THBAR = 'thbar';
    case XTZ = 'xtz';
    case TXTZ = 'txtz';

    /**
     * How many decimal places the coin has, as BitGo lists it, for Amount. A testnet can differ from its mainnet:
     * Polkadot has 10 and its testnet 12.
     */
    public function decimals(): int
    {
        // No default arm, so PHPStan fails if a new case has no decimals.
        return match ($this) {
            self::BTC, self::TBTC, self::TBTC4, self::BCH, self::TBCH, self::LTC, self::TLTC, self::DOGE, self::TDOGE,
            self::DASH, self::TDASH, self::ZEC, self::TZEC, self::APT, self::TAPT, self::HBAR, self::THBAR => 8,
            self::XLM, self::TXLM => 7,
            self::XRP, self::TXRP, self::TRX, self::TTRX, self::ALGO, self::TALGO, self::ADA, self::TADA,
            self::ATOM, self::TATOM, self::XTZ, self::TXTZ => 6,
            self::SOL, self::TSOL, self::SUI, self::TSUI, self::TON, self::TTON => 9,
            self::DOT => 10,
            self::TDOT => 12,
            self::NEAR, self::TNEAR => 24,
            self::ETH, self::HTETH, self::SEPETH, self::POLYGON, self::TPOLYGON, self::ARBETH, self::TARBETH,
            self::OPETH, self::TOPETH, self::BASEETH, self::TBASEETH, self::BSC, self::TBSC, self::AVAXC,
            self::TAVAXC => 18,
        };
    }
}
