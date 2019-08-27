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
use FurqanSiddiqui\Rippled\RPC\Transaction\PaymentTransaction;

/**
 * Class Transaction
 * @package FurqanSiddiqui\Rippled\RPC
 */
class Transaction extends AbstractResultModel
{
    /** @var null|string */
    public $hash;
    /** @var string */
    public $account;
    /** @var string */
    public $transactionType;
    /** @var RippledAmountObj */
    public $fee;
    /** @var int */
    public $sequence;
    /** @var null|string */
    public $accountTxId;
    /** @var int */
    public $flags;
    /** @var int */
    public $lastLedgerSequence;
    /** @var null|array */
    public $memos;
    /** @var null|array */
    public $signers;
    /** @var null|int */
    public $sourceTag;
    /** @var string */
    public $signingPubKey;
    /** @var null|string */
    public $txnSignature;
    /** @var null|int */
    public $ledgerIndex;
    /** @var null|array */
    public $meta;

    /**
     * @param array $res
     * @param bool $resCaseConversion
     * @return Transaction|PaymentTransaction
     * @throws \FurqanSiddiqui\Rippled\Exception\ResponseParseException
     */
    public static function ConstructPerType(array $res, bool $resCaseConversion = true)
    {
        $transactionType = $res["TransactionType"] ?? null;
        if (!$transactionType || !in_array($transactionType, \FurqanSiddiqui\Rippled\Validator::TRANSACTION_TYPES)) {
            throw new \UnexpectedValueException('Invalid transaction type');
        }

        switch ($transactionType) {
            case "Payment":
                $txObjectClass = 'FurqanSiddiqui\Rippled\RPC\Transaction\PaymentTransaction';
                break;
            default:
                $txObjectClass = get_class();
                break;
        }


        if ($resCaseConversion) {
            $res = self::ResultArrayCaseConversion($res);
        }

        /** @var Transaction $obj */
        $obj = new $txObjectClass();
        $obj->mapResultToObject($res);

        /** @var string $fee */
        $fee = $obj->fee;
        $obj->fee = new RippledAmountObj($fee);
        if ($obj instanceof PaymentTransaction) {
            if (is_string($obj->amount)) {
                $obj->amount = new RippledAmountObj($obj->amount);
            } elseif (is_array($obj->amount)) {
                $obj->amount = new RippledIssuedTokenObj($obj->amount);
            }
        }

        return $obj;
    }

    /**
     * @param ObjectMapper $objectMapper
     */
    public function objectMapperProps(ObjectMapper $objectMapper): void
    {
        $objectMapper->prop("hash")->dataTypes("string")->nullable()->validate(function ($value) {
            return Validator::String($value)->match('/^[a-f0-9]{64}$/i')->validate();
        });

        $objectMapper->prop("account")->dataTypes("string");
        $objectMapper->prop("transactionType")->dataTypes("string")->validate(function ($type) {
            return Validator::String($type)->inArray(\FurqanSiddiqui\Rippled\Validator::TRANSACTION_TYPES)->validate();
        });

        $objectMapper->prop("fee")->dataTypes("string")->validate(function ($value) {
            return Validator::Numeric($value)->scale(0)->validate()->value();
        });

        $objectMapper->prop("sequence")->dataTypes("integer")->validate(function ($value) {
            return Validator::Integer($value)->range(0, \FurqanSiddiqui\Rippled\Validator::UINT32_MAX)->validate();
        });

        $objectMapper->prop("flags")->dataTypes("integer");

        $objectMapper->prop("accountTxId")->dataTypes("string")->nullable()->validate(function ($value) {
            return Validator::String($value)->match('/^[a-f0-9]{64}$/i')->validate();
        });

        $objectMapper->prop("lastLedgerSequence")->dataTypes("integer")->nullable()->validate(function ($value) {
            return Validator::Integer($value)->range(0, \FurqanSiddiqui\Rippled\Validator::UINT32_MAX)->validate();
        });

        $objectMapper->prop("memos")->nullable()->dataTypes("array");

        $objectMapper->prop("signers")->nullable()->dataTypes("array");

        $objectMapper->prop("sourceTag")->nullable()->dataTypes("integer")->validate(function ($value) {
            return Validator::Integer($value)->range(0, \FurqanSiddiqui\Rippled\Validator::UINT32_MAX)->validate();
        });

        $objectMapper->prop("signingPubKey")->dataTypes("string");

        $objectMapper->prop("txnSignature")->dataTypes("string");

        $objectMapper->prop("ledgerIndex")->nullable()->dataTypes("integer")->validate(function ($value) {
            return Validator::Integer($value)->range(0, \FurqanSiddiqui\Rippled\Validator::UINT32_MAX)->validate();
        });
    }
}