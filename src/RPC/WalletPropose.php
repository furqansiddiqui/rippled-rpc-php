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

use Comely\DataTypes\Buffer\Base16;
use Comely\Utils\OOP\ObjectMapper;
use Comely\Utils\Validator\Validator;

/**
 * Class WalletPropose
 * @package FurqanSiddiqui\Rippled\RPC
 */
class WalletPropose extends AbstractResultModel
{
    /** @var string */
    public $accountId;
    /** @var string */
    public $keyType;
    /** @var string */
    public $masterKey;
    /** @var string */
    public $masterSeed;
    /** @var string */
    public $masterSeedHex;
    /** @var string */
    public $publicKey;
    /** @var string */
    public $publicKeyHex;

    public function objectMapperProps(ObjectMapper $objectMapper): void
    {
        $objectMapper->prop("accountId")->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->match(\FurqanSiddiqui\Rippled\Validator::MATCH_ACCOUNT_ID)->len(25, 35)->validate();
        });

        $objectMapper->prop("keyType")->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->lowerCase()->inArray(\FurqanSiddiqui\Rippled\Validator::KEY_TYPES)->validate();
        });

        $objectMapper->prop("masterKey")->dataTypes("string");
        $objectMapper->prop("masterSeed")->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->match(\FurqanSiddiqui\Rippled\Validator::MATCH_ACCOUNT_SECRET)->len(8, 1024)->validate();
        });

        $objectMapper->prop("masterSeedHex")->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->match('/^[a-f0-9]{2,}$/i')->validate();
        });

        $this->masterSeedHex = new Base16($this->masterSeedHex);

        $objectMapper->prop("publicKey")->dataTypes("string");
        $objectMapper->prop("publicKeyHex")->dataTypes("string");
    }
}
