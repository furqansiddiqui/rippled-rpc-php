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

use Comely\Utils\OOP\ObjectMapper;
use Comely\Utils\Validator\Validator;

/**
 * Class AccountInfo
 * @package FurqanSiddiqui\Rippled\RPC
 */
class AccountInfo extends AbstractResultModel
{
    /** @var string */
    public $account;
    /** @var RippledAmountObj */
    public $balance;
    /** @var int */
    public $flags;
    /** @var string */
    public $ledgerEntryType;
    /** @var int */
    public $ownerCount;
    /** @var null|string */
    public $previousTxnID;
    /** @var null|int */
    public $previousTxnLgrSeq;
    /** @var int */
    public $sequence;
    /** @var string|null */
    public $index;

    /** @var null|int */
    public $ledgerIndex;
    /** @var null|int */
    public $ledgerCurrentIndex;
    /** @var null|string */
    public $ledgerHash;
    /** @var bool */
    public $validated;

    /**
     * @param ObjectMapper $objectMapper
     */
    public function objectMapperProps(ObjectMapper $objectMapper): void
    {
        $objectMapper->prop("account")->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->match(\FurqanSiddiqui\Rippled\Validator::MATCH_ACCOUNT_ID)->validate();
        });

        $objectMapper->prop("balance")->dataTypes("string")->validate(function ($value) {
            return Validator::Numeric($value)->scale(0)->validate();
        });

        $objectMapper->prop("flags")->dataTypes("integer");
        $objectMapper->prop("ledgerEntryType")->dataTypes("string");
        $objectMapper->prop("ownerCount")->dataTypes("integer")->validate(function ($value) {
            return Validator::Integer($value)->range(0, \FurqanSiddiqui\Rippled\Validator::UINT32_MAX)->validate();
        });

        $objectMapper->prop("previousTxnID")->dataTypes("string")->nullable();
        $objectMapper->prop("previousTxnLgrSeq")->dataTypes("integer")->nullable();

        $objectMapper->prop("sequence")->dataTypes("integer")->validate(function ($value) {
            return Validator::Integer($value)->range(0, \FurqanSiddiqui\Rippled\Validator::UINT32_MAX)->validate();
        });

        $objectMapper->prop("index")->dataTypes("string")->nullable();

        $objectMapper->prop("ledgerHash")->dataTypes("string")->nullable();
        $objectMapper->prop("ledgerIndex")->dataTypes("integer")->nullable();
        $objectMapper->prop("ledgerCurrentIndex")->dataTypes("integer")->nullable();

        $objectMapper->prop("validated")->dataTypes("boolean");
    }
}