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

use FurqanSiddiqui\Rippled\RPC\AccountInfo;
use FurqanSiddiqui\Rippled\RPC\RippledAmountObj;

/**
 * Class Account
 * @package FurqanSiddiqui\Rippled
 */
class Account
{
    /** @var RippledRPC */
    private $rippledRPC;
    /** @var string */
    private $accountId;

    /**
     * Account constructor.
     * @param RippledRPC $rippledRPC
     * @param string $accountId
     */
    public function __construct(RippledRPC $rippledRPC, string $accountId)
    {
        $this->rippledRPC = $rippledRPC;
        $this->accountId = $accountId;
    }

    /**
     * @param bool $strict
     * @param string|null $ledger
     * @return AccountInfo
     * @throws Exception\APIQueryException
     * @throws Exception\ResponseParseException
     */
    public function info(bool $strict = true, ?string $ledger = "validated")
    {
        $params = [
            "account" => $this->accountId,
            "strict" => $strict,
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

        $accInfo = $this->rippledRPC->request("account_info", $params);
        $accInfoObj = new AccountInfo();
        $accInfoObj->mapResultToObject($accInfo->result());
        /** @var string $balance */
        $balance = $accInfoObj->balance;
        $accInfoObj->balance = new RippledAmountObj($balance);

        return $accInfoObj;
    }
}