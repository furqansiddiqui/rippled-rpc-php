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

use Comely\DataTypes\Buffer\Base16;
use FurqanSiddiqui\Rippled\Exception\APIQueryException;
use FurqanSiddiqui\Rippled\RPC\AccountInfo;
use FurqanSiddiqui\Rippled\RPC\RippledAmountObj;
use FurqanSiddiqui\Rippled\RPC\RippledIssuedTokenObj;

/**
 * Class Account
 * @package FurqanSiddiqui\Rippled
 */
class Account
{
    public const UNLOCK_PASSPHRASE = 0x0a;
    public const UNLOCK_SEED_HEX = 0x0b;

    /** @var RippledRPC */
    private $rippledRPC;
    /** @var string */
    private $accountId;
    /** @var bool */
    private $strict;
    /** @var null|int */
    private $unlock;
    /** @var null|string|Base16 */
    private $unlockValue;
    /** @var null|string */
    private $keyType;

    /**
     * Account constructor.
     * @param RippledRPC $rippledRPC
     * @param string $accountId
     * @param bool $strict
     */
    public function __construct(RippledRPC $rippledRPC, string $accountId, bool $strict = true)
    {
        $this->rippledRPC = $rippledRPC;
        $this->accountId = $accountId;
        $this->strict = $strict;
    }

    /**
     * @param string|null $ledger
     * @return AccountInfo
     * @throws Exception\APIQueryException
     * @throws Exception\ResponseParseException
     */
    public function info(?string $ledger = "validated"): AccountInfo
    {
        $params = [
            "account" => $this->accountId,
            "strict" => $this->strict,
        ];

        if ($ledger) {
            if (preg_match('/^[0-9]+$/', $ledger)) {
                if (bccomp($ledger, "0", 0) !== 1 || bccomp($ledger, strval(PHP_INT_MAX), 0) === 1) {
                    throw new \OutOfRangeException('Ledger index is out of range');
                }

                $params["ledger"] = intval($ledger);
            } elseif (preg_match('/^[a-f0-9]+$/i', $ledger)) {
                if (strlen($ledger) !== 40) {
                    throw new \InvalidArgumentException('Value of param "ledger" must be precisely 20 bytes');
                }

                $params["ledger_hash"] = $ledger;
            } else {
                $ledger = strtolower($ledger);
                if (!in_array($ledger, Validator::LEDGER_TYPES)) {
                    throw new \OutOfBoundsException('Invalid ledger index');
                }

                $params["ledger"] = $ledger;
            }
        }

        $req = $this->rippledRPC->request("account_info", $params);
        $result = $req->result()->array();
        $resultAccData = $result["account_data"] ?? null;
        if (!is_array($resultAccData)) {
            throw new \UnexpectedValueException('Object "account_data" not found in result');
        }

        unset($result["account_data"]);
        $resultAccData = array_merge($resultAccData, $result);
        $accInfoObj = new AccountInfo();
        $accInfoObj->mapResultToObject(AccountInfo::ResultArrayCaseConversion($resultAccData));
        /** @var string $balance */
        $balance = $accInfoObj->balance;
        $accInfoObj->balance = new RippledAmountObj($balance);

        return $accInfoObj;
    }

    /**
     * @param Base16 $seed
     * @param string $keyType
     * @return Account
     */
    public function unlockWithSeed(Base16 $seed, string $keyType = "secp256k1"): self
    {
        if (!in_array($keyType, Validator::KEY_TYPES)) {
            throw new \OutOfBoundsException('Invalid key type');
        }

        $this->unlock = self::UNLOCK_SEED_HEX;
        $this->unlockValue = $seed;
        $this->keyType = $keyType;
        return $this;
    }

    /**
     * @param string $passphrase
     * @param string $keyType
     * @return Account
     */
    public function unlockWithPassphrase(string $passphrase, string $keyType = "secp256k1"): self
    {
        if (!in_array($keyType, Validator::KEY_TYPES)) {
            throw new \OutOfBoundsException('Invalid key type');
        }

        $this->unlock = self::UNLOCK_PASSPHRASE;
        $this->unlockValue = $passphrase;
        $this->keyType = $keyType;
        return $this;
    }

    /**
     * @param string $dest
     * @param RippledIssuedTokenObj $amount
     * @param int|null $destTag
     * @param int|null $sourceTag
     * @param int $feeMulMax
     * @param bool $offline
     * @return Base16
     * @throws APIQueryException
     */
    public function payment(string $dest, RippledIssuedTokenObj $amount, ?int $destTag = null, ?int $sourceTag = null, int $feeMulMax = 100, bool $offline = false): Base16
    {
        if (!preg_match(Validator::MATCH_ACCOUNT_ID, $dest)) {
            throw new APIQueryException('Invalid destination XRP address');
        }

        $params = [
            "offline" => $offline,
            "fee_mult_max" => $feeMulMax
        ];

        switch ($this->unlock) {
            case self::UNLOCK_PASSPHRASE:
                $params["passphrase"] = $this->unlockValue;
                $params["key_type"] = $this->keyType;
                break;
            case self::UNLOCK_SEED_HEX:
                $params["seed_hex"] = $this->unlockValue->hexits(false);
                $params["key_type"] = $this->keyType;
                break;
            default:
                throw new APIQueryException('Account passphrase or seed value must be set', APIQueryException::ACCOUNT_NOT_UNLOCKED);
        }

        $tx = [];
        $tx["TransactionType"] = "Payment";
        $tx["Account"] = $this->accountId;

        if ($amount instanceof RippledAmountObj) {
            $tx["Amount"] = $amount->drops;
        } elseif ($amount instanceof RippledIssuedTokenObj) {
            $tx["Amount"] = $amount->array();
        }

        $tx["Destination"] = $dest;
        if ($destTag) {
            $tx["DestinationTag"] = $destTag;
        }

        if ($sourceTag) {
            $tx["SourceTag"] = $sourceTag;
        }

        $params["tx_json"] = $tx;

        // Sign Transaction
        try {
            $sign = $this->rippledRPC->request("sign", $params);
        } catch (APIQueryException $e) {
            if ($e->getCode() === APIQueryException::TRANSACTION_NEED_MORE_FEE) {
                throw new APIQueryException('Transaction requires higher fee amount as per current network load', $e->getCode());
            }

            throw $e;
        }

        $blob = $sign->result()->get("tx_blob");
        if (!$blob) {
            throw new APIQueryException('Signed transaction blob not returned in response');
        }

        $submit = $this->rippledRPC->request("submit", ["tx_blob" => $blob]);
        $result = $submit->result()->array();
        $engineResult = $result["engine_result"] ?? null;
        if ($engineResult !== "tesSUCCESS") {
            throw new APIQueryException(sprintf('Transaction submit fail with error code "%s"', $engineResult));
        }

        $txJSON = $result["tx_json"] ?? null;
        if (!is_array($txJSON)) {
            throw new APIQueryException('Transaction submit did not return txJSON block; Warning transaction MAY HAVE ALREADY been sent!');
        }

        $txHash = $txJSON["hash"] ?? null;
        if (!is_string($txHash) || !preg_match('/^[a-f0-9]{64}$/i', $txHash)) {
            throw new APIQueryException('Transaction submit did not return hash; Warning transaction MAY HAVE ALREADY been sent!');
        }

        return new Base16($txHash);
    }
}