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
 * BitGo coin codes, as used in the API path (/api/v2/{coin}/...). BitGo supports many more coins and tokens than
 * are listed here, and any BitGo coin code can be passed as a string.
 */
abstract class CurrencyCode
{
    // Mainnet
    const ALGORAND                  = 'algo';
    const APTOS                     = 'apt';
    const ARBITRUM                  = 'arbeth';
    const AVALANCHE_C_CHAIN         = 'avaxc';
    const AVALANCHE_P_CHAIN         = 'avaxp';
    const BITCOIN                   = 'btc';
    const BITCOIN_CASH              = 'bch';
    const BITCOIN_GOLD              = 'btg';
    const BNB_SMART_CHAIN           = 'bsc';
    const CARDANO                   = 'ada';
    const CASPER                    = 'cspr';
    const CELO                      = 'celo';
    const COSMOS                    = 'atom';
    const DASH                      = 'dash';
    const DOGECOIN                  = 'doge';
    const EOS                       = 'eos';
    const ETHEREUM                  = 'eth';
    const HEDERA                    = 'hbar';
    const LITECOIN                  = 'ltc';
    const NEAR                      = 'near';
    const OPTIMISM                  = 'opeth';
    const POLKADOT                  = 'dot';
    const POLYGON                   = 'polygon';
    const RSK_SMART_BTC             = 'rbtc';
    const SOLANA                    = 'sol';
    const STACKS                    = 'stx';
    const STELLAR                   = 'xlm';
    const SUI                       = 'sui';
    const TEZOS                     = 'xtz';
    const TON                       = 'ton';
    const TRON                      = 'trx';
    const RIPPLE                    = 'xrp';
    const ZCASH                     = 'zec';

    /**
     * @deprecated 2.3.0 'avax' is not a BitGo coin code. Use AVALANCHE_C_CHAIN ('avaxc').
     */
    const AVAX                      = 'avax';

    // Testnet
    const ALGORAND_TESTNET          = 'talgo';
    const APTOS_TESTNET             = 'tapt';
    const ARBITRUM_TESTNET          = 'tarbeth';
    const AVALANCHE_C_CHAIN_TESTNET = 'tavaxc';
    const AVALANCHE_P_CHAIN_TESTNET = 'tavaxp';
    const BITCOIN_TESTNET           = 'tbtc';
    const BITCOIN_TESTNET4          = 'tbtc4';
    const BITCOIN_SIGNET            = 'tbtcsig';
    const BITCOIN_CASH_TESTNET      = 'tbch';
    const BITCOIN_GOLD_TESTNET      = 'tbtg';
    const BNB_SMART_CHAIN_TESTNET   = 'tbsc';
    const CARDANO_TESTNET           = 'tada';
    const CASPER_TESTNET            = 'tcspr';
    const CELO_TESTNET              = 'tcelo';
    const COSMOS_TESTNET            = 'tatom';
    const DASH_TESTNET              = 'tdash';
    const DOGECOIN_TESTNET          = 'tdoge';
    const EOS_TESTNET               = 'teos';
    const ETHEREUM_HOODI_TESTNET    = 'hteth';
    const ETHEREUM_SEPOLIA_TESTNET  = 'sepeth';
    const HEDERA_TESTNET            = 'thbar';
    const LITECOIN_TESTNET          = 'tltc';
    const NEAR_TESTNET              = 'tnear';
    const OPTIMISM_TESTNET          = 'topeth';
    const POLKADOT_TESTNET          = 'tdot';
    const POLYGON_TESTNET           = 'tpolygon';
    const RSK_SMART_BTC_TESTNET     = 'trbtc';
    const SOLANA_TESTNET            = 'tsol';
    const STACKS_TESTNET            = 'tstx';
    const STELLAR_TESTNET           = 'txlm';
    const SUI_TESTNET               = 'tsui';
    const TEZOS_TESTNET             = 'txtz';
    const TON_TESTNET               = 'tton';
    const TRON_TESTNET              = 'ttrx';
    const RIPPLE_TESTNET            = 'txrp';
    const ZCASH_TESTNET             = 'tzec';

    /**
     * @deprecated 2.3.0 'tavax' is not a BitGo coin code. Use AVALANCHE_C_CHAIN_TESTNET ('tavaxc').
     */
    const AVAX_TESTNET              = 'tavax';

    /**
     * @deprecated 2.3.0 'teth' is the retired Kovan testnet. Use ETHEREUM_HOODI_TESTNET ('hteth') or ETHEREUM_SEPOLIA_TESTNET ('sepeth').
     */
    const ETHEREUM_TESTNET          = 'teth';

    //ERC20 TOKENS
    const ERC = 'erc'; //BitGo’s sample ERC20 token
    const AAVE = 'aave';
    const AUGUR_REPV2 = 'repv2';
    const BASIC_ATTENTION_TOKEN = 'bat';
    const CHAINLINK = 'link';
    const CIVIC_REP = 'cvc';
    const DAI = 'dai';
    const FUNFAIR = 'fun';
    const GOLEM_GLM = 'glm';
    const KYBER_NETWORK_V2 = 'knc2';
    const NUMERAIRE = 'nmr';
    const TETHER_USDT = 'usdt';
    const UNISWAP = 'uni';
    const USD_COIN = 'usdc';
    const WRAPPED_BITCOIN = 'wbtc';
    const ZRX = 'zrx'; //0x token

    /**
     * @deprecated 2.3.0 The OMG Network has wound down and the token is widely delisted.
     */
    const OMISE_GO = 'omg';

    /**
     * @deprecated 2.3.0 Golem migrated from GNT to GLM. Use GOLEM_GLM.
     */
    const GOLEM = 'gnt';

    /**
     * @deprecated 2.3.0 Augur migrated from REP to REPv2. Use AUGUR_REPV2.
     */
    const AUGUR_REP = 'rep';

    /**
     * @deprecated 2.3.0 Kyber Network migrated KNC to a new token contract. Use KYBER_NETWORK_V2.
     */
    const KYBER_NETWORK = 'knc';

    /**
     * @deprecated 2.3.0 The ERC-20 QRL token was swapped for coins on QRL's own chain.
     */
    const QRL = 'qrl';

    /**
     * @deprecated 2.3.0 TenX has wound down and the token is widely delisted.
     */
    const TENXPAY = 'pay';

    /**
     * @deprecated 2.3.0 BRD was acquired by Coinbase, and the token is discontinued and widely delisted.
     */
    const BREAD = 'brd';
}
