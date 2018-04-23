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

abstract class CurrencyCode {    
    //PRODUCTION
    const BITCOIN = 'btc';
    const BITCOIN_CASH = 'bch';
    const BITCOIN_GOLD = 'btg';
    const ETHEREUM = 'eth';
    const LITECOIN = 'ltc';
    const RIPPLE = 'xrp';
    const ROYAL_MINT_GOLD = 'rmg';
    
    //ERC20 TOKENS
    const ERC = 'erc'; //BitGo’s sample ERC20 token
    const OMISE_GO = 'omg';
    const ZRX = 'zrx'; //0x token
    const FUNFAIR = 'fun';
    const GOLEM = 'gnt';
    const AUGUR_REP = 'rep';
    const BASIC_ATTENTION_TOKEN = 'bat';
    const KYBER_NETWORK = 'knc';
    const CIVIC_REP = 'cvc';
    const EOS = 'eos';
    const QRL = 'qrl';
    const NUMERAIRE = 'nmr'; 
    const TENXPAY = 'pay';
    const BREAD = 'brd';
    
    //TESTNET
    const BITCOIN_TESTNET = 'tbtc';
    const BITCOIN_CASH_TESTNET = 'tbch';
    const ETHEREUM_TESTNET = 'teth';
    const LITECOIN_TESTNET = 'tltc';
    const RIPPLE_TESTNET = 'txrp';
    const ROYAL_MINT_GOLD_TESTNET = 'trmg';
    const ERC_TESTNET = 'terc';
}