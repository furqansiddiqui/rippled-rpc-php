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

use Comely\DataTypes\BcNumber;
use FurqanSiddiqui\Rippled\Validator;

/**
 * Class RippledAmountObj
 * @package FurqanSiddiqui\Rippled\RPC
 */
class RippledAmountObj
{
    /** @var string */
    public $drops;
    /** @var string */
    public $xrp;
    /** @var int */
    public $scale;

    /**
     * RippledAmountObj constructor.
     * @param string $amount
     * @param int|null $scale
     */
    public function __construct(string $amount, int $scale = Validator::DEC_SCALE)
    {
        $this->scale = $scale;

        $amount = new BcNumber($amount);
        if (!$amount->isInteger()) {;
            $this->xrp = $amount->value();
            $this->drops = $amount->mulPow(10, $this->scale, 6)->value();
        } else {
            $this->drops = $amount;
            $this->xrp = bcdiv($amount->value(), bcpow("10", strval($this->scale), 0), 0);
        }
    }
}