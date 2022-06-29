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
 * @version 2.2
 */

namespace neto737\BitGoSDK\Enum;

abstract class CurrencyCode {

    // Mainnet
    const ALGORAND                  = 'algo';
    const AVAX                      = 'avax';
    const BITCOIN                   = 'btc';
    const BITCOIN_CASH              = 'bch';
    const BITCOIN_GOLD              = 'btg';
    const CASPER                    = 'cspr';
    const CELO                      = 'celo';
    const DASH                      = 'dash';
    const EOS                       = 'eos';
    const ETHEREUM                  = 'eth';
    const HEDERA                    = 'hbar';
    const LITECOIN                  = 'ltc';
    const RSK_SMART_BTC             = 'rbtc';
    const STACKS                    = 'stx';
    const STELLAR                   = 'xlm';
    const TEZOS                     = 'xtz';
    const TRON                      = 'trx';
    const RIPPLE                    = 'xrp';
    const ZCASH                     = 'zec';

    // Testnet
    const ALGORAND_TESTNET          = 'talgo';
    const AVAX_TESTNET              = 'tavax';
    const BITCOIN_TESTNET           = 'tbtc';
    const BITCOIN_CASH_TESTNET      = 'tbch';
    const CASPER_TESTNET            = 'tcspr';
    const CELO_TESTNET              = 'tcelo';
    const DASH_TESTNET              = 'tdash';
    const EOS_TESTNET               = 'teos';
    const ETHEREUM_TESTNET          = 'teth';
    const HEDERA_TESTNET            = 'thbar';
    const LITECOIN_TESTNET          = 'tltc';
    const RSK_SMART_BTC_TESTNET     = 'trbtc';
    const STACKS_TESTNET            = 'tstx';
    const STELLAR_TESTNET           = 'txlm';
    const TEZOS_TESTNET             = 'txtz';
    const TRON_TESTNET              = 'ttrx';
    const RIPPLE_TESTNET            = 'txrp';
    const ZCASH_TESTNET             = 'tzec';
    
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
    const QRL = 'qrl';
    const NUMERAIRE = 'nmr';
    const TENXPAY = 'pay';
    const BREAD = 'brd';
}
