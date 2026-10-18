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

namespace neto737\BitGoSDK\Enum;

/**
 * BitGo address chains for UTXO wallets. Even chains are receive (deposit) addresses, odd chains are change addresses.
 * Which chains a wallet supports depends on the coin: chains 10 and up need SegWit, 30 and up need Taproot.
 */
abstract class AddressType
{
    // P2SH multisig, the original BitGo address format
    const LEGACY_DEPOSIT        = 0;
    const LEGACY_CHANGE         = 1;

    // SegWit multisig wrapped in P2SH (P2SH-P2WSH)
    const P2SH_P2WSH_DEPOSIT    = 10;
    const P2SH_P2WSH_CHANGE     = 11;

    // Native SegWit multisig (P2WSH, bech32 addresses)
    const P2WSH_DEPOSIT         = 20;
    const P2WSH_CHANGE          = 21;

    // Taproot multisig (P2TR, bech32m addresses)
    const P2TR_DEPOSIT          = 30;
    const P2TR_CHANGE           = 31;

    // Same values as P2WSH_DEPOSIT and P2WSH_CHANGE
    const BECH32_DEPOSIT        = 20;
    const BECH32_CHANGE         = 21;

    // Same values as P2TR_DEPOSIT and P2TR_CHANGE
    const BECH32M_DEPOSIT       = 30;
    const BECH32M_CHANGE        = 31;

    /**
     * @deprecated 2.3.0 Chain 10 is SegWit wrapped in P2SH, not plain P2SH. Use P2SH_P2WSH_DEPOSIT, or LEGACY_DEPOSIT for plain P2SH.
     */
    const P2SH_DEPOSIT          = 10;

    /**
     * @deprecated 2.3.0 Chain 11 is SegWit wrapped in P2SH, not plain P2SH. Use P2SH_P2WSH_CHANGE, or LEGACY_CHANGE for plain P2SH.
     */
    const P2SH_CHANGE           = 11;
}
