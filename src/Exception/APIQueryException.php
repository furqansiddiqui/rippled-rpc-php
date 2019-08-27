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

namespace FurqanSiddiqui\Rippled\Exception;

/**
 * Class APIQueryException
 * @package FurqanSiddiqui\Rippled\Exception
 */
class APIQueryException extends RippledRPCException
{
    public const ACCOUNT_NOT_FOUND = 0x2af8;
    public const TRANSACTION_NOT_FOUND = 0x2ee0;
    public const ACCOUNT_NOT_UNLOCKED = 0x32c8;
    public const TRANSACTION_NEED_MORE_FEE = 0x36b0;

    public const SIGNALS = [
        "actNotFound" => self::ACCOUNT_NOT_FOUND,
        "txnNotFound" => self::TRANSACTION_NOT_FOUND,
        "highFee" => self::TRANSACTION_NEED_MORE_FEE
    ];
}