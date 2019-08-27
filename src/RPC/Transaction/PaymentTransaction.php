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

namespace FurqanSiddiqui\Rippled\RPC\Transaction;

use Comely\Utils\OOP\ObjectMapper;
use Comely\Utils\Validator\Validator;
use FurqanSiddiqui\Rippled\RPC\RippledAmountObj;
use FurqanSiddiqui\Rippled\RPC\Transaction;

/**
 * Class PaymentTransaction
 * @package FurqanSiddiqui\Rippled\RPC\Transaction
 */
class PaymentTransaction extends Transaction
{
    /** @var RippledAmountObj */
    public $amount;
    /** @var string */
    public $destination;
    /** @var null|int */
    public $destinationTag;
    /** @var null|string */
    public $invoiceId;
    /** @var array */
    public $paths;
    /** @var null|string */
    public $sendMax;
    /** @var null|string */
    public $deliverMin;

    /**
     * @param ObjectMapper $objectMapper
     */
    public function objectMapperProps(ObjectMapper $objectMapper): void
    {
        parent::objectMapperProps($objectMapper);

        $objectMapper->prop("amount")->dataTypes("string", "array");

        $objectMapper->prop("destination")->dataTypes("string");

        $objectMapper->prop("destinationTag")->nullable()->dataTypes("integer")->validate(function ($value) {
            return Validator::Integer($value)->range(0, \FurqanSiddiqui\Rippled\Validator::UINT32_MAX)->validate();
        });

        $objectMapper->prop("invoiceId")->dataTypes("string")->nullable()->validate(function ($value) {
            return Validator::String($value)->match('/^[a-f0-9]{64}$/i')->validate();
        });

        $objectMapper->prop("paths")->nullable()->dataTypes("array");

        $objectMapper->prop("sendMax")->nullable()->dataTypes("string")->validate(function ($value) {
            return Validator::Numeric($value)->scale(0)->validate()->value();
        });

        $objectMapper->prop("deliverMin")->nullable()->dataTypes("string")->validate(function ($value) {
            return Validator::Numeric($value)->scale(0)->validate()->value();
        });
    }
}