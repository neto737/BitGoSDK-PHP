<?php

namespace neto737\BitGoSDK\Enum;

abstract class AddressTypes
{
    // Legacy address format
    const P2SH_DEPOSIT          = 0;
    const P2SH_CHANGE           = 1;

    // Wrapped Segwit format
    const P2SH_P2WSH_DEPOSIT    = 10;
    const P2SH_P2WSH_CHANGE     = 11;

    // Bech32 format (Native segwit)
    const P2WSH_DEPOSIT         = 20;
    const P2WSH_CHANGE          = 21;

    // Bech32m format (Taproot fork)
    const P2TR_DEPOSIT          = 30;
    const P2TR_CHANGE           = 31;
}
