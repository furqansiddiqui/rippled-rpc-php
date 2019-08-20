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

namespace FurqanSiddiqui\Rippled;

/**
 * Class Validator
 * @package FurqanSiddiqui\Rippled
 */
class Validator
{
    public const DEC_SCALE = 6;
    public const MATCH_ACCOUNT_ID = '/^r[a-z0-9]{24,34}$/i';
    public const MATCH_ACCOUNT_SECRET = '/^s[a-z0-9]+$/i';
    public const KEY_TYPES = ['secp256k1', 'ed25519'];
    public const LEDGER_TYPES = ["validated", "closed", "current"];
    public const UINT32_MAX = 4294967295;
}