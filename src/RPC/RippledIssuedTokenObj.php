<?php
/**
 * This file is a part of "furqansiddiqui/rippled-rpc-php" package.
 * https://github.com/furqansiddiqui/rippled-rpc-php
 *
 * Copyright (c) 2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/furqansiddiqui/rippled-rpc-php/blob/master/LICENSE
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Rippled\RPC;

use FurqanSiddiqui\Rippled\Validator;

/**
 * Class RippledIssuedTokenObj
 * @package FurqanSiddiqui\Rippled\RPC
 */
class RippledIssuedTokenObj
{
    /** @var string */
    public $currency;
    /** @var string */
    public $value;
    /** @var string */
    public $issuer;

    /**
     * RippledIssuedTokenObj constructor.
     * @param array $amount
     */
    public function __construct(array $amount)
    {
        if (array_key_exists("currency", $amount)) {
            if (preg_match('/[a-z0-9\s\_\!\#\@\$\(\)\[\]\%\&\^]{1,16}/', $amount["currency"])) {
                $this->currency = $amount["currency"];
            }
        }

        if (array_key_exists("value", $amount)) {
            if (preg_match('/^[0-9]+(\.[0-9]+)?$/', $amount["value"])) {
                $this->value = $amount["value"];
            }
        }

        if (array_key_exists("issuer", $amount)) {
            if (preg_match(Validator::MATCH_ACCOUNT_ID, $amount["issuer"])) {
                $this->issuer = $amount["issuer"];
            }
        }
    }
}