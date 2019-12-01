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
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3
 * @version 2.1
 */

namespace neto737\BitGoSDK\Enum;

class AddressType {
    
    const LEGACY_DEPOSIT = 0;
    const LEGACY_CHANGE = 1;
    
    const P2SH_DEPOSIT = 10;
    const P2SH_CHANGE = 11;
    
    const BECH32_DEPOSIT = 20;
    const BECH32_CHANGE = 21;
}