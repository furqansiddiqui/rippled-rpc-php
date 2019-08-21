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
use FurqanSiddiqui\Rippled\Exception\ConnectionException;
use FurqanSiddiqui\Rippled\Exception\ResponseParseException;
use FurqanSiddiqui\Rippled\RPC\Transaction;
use FurqanSiddiqui\Rippled\RPC\WalletPropose;
use FurqanSiddiqui\Rippled\Server\APIQueryResult;
use FurqanSiddiqui\Rippled\Server\Result;
use FurqanSiddiqui\Rippled\Server\SSL;
use HttpClient\Exception\HttpClientException;
use HttpClient\Request;
use HttpClient\Response\JSONResponse;
use HttpClient\Response\Response;

/**
 * Class RippledRPC
 * @package FurqanSiddiqui\Rippled
 */
class RippledRPC
{
    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var bool */
    private $https;
    /** @var null */
    private $sslConfig;
    /** @var bool */
    private $wasConnected;

    /**
     * RippledRPC constructor.
     * @param string $host
     * @param int $port
     * @param bool $ssl
     */
    public function __construct(string $host, int $port, bool $ssl = false)
    {
        if (!preg_match('/^[a-z]+[a-z0-9\-]+(\.[a-z]+[a-z0-9\-]+)*$/i', $host)) {
            if (!filter_var($host, FILTER_VALIDATE_IP)) {
                throw new \InvalidArgumentException('Invalid XRP node hostname/IP address');
            }
        }

        if ($port < 0x3e8 || $port >= 0xffff) {
            throw new \OutOfRangeException('Invalid XRP node port');
        }

        if ($ssl) {
            $this->sslConfig = new SSL();
        }

        $this->https = $ssl;
        $this->host = $host;
        $this->port = $port;
        $this->wasConnected = false;
    }

    /**
     * @return SSL
     */
    public function ssl(): SSL
    {
        if (!$this->sslConfig) {
            throw new \OutOfBoundsException('SSL config is not available when not using HTTPS');
        }

        return $this->sslConfig;
    }

    /**
     * @return bool
     * @throws APIQueryException
     */
    public function ping(): bool
    {
        $req = $this->request("ping", ["ping" => 1]);
        if (!$req->result() || !$req->result()->isSuccess()) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     * @throws APIQueryException
     */
    public function serverInfo(): array
    {
        $req = $this->request("server_info", ["server_info" => 1]);
        return $req->result()->array();
    }

    /**
     * @param string $accountId
     * @param bool $strict
     * @return Account
     */
    public function account(string $accountId, bool $strict = true): Account
    {
        return new Account($this, $accountId, $strict);
    }

    /**
     * @param Base16 $txId
     * @return Transaction
     * @throws APIQueryException
     * @throws ResponseParseException
     */
    public function transaction(Base16 $txId): Transaction
    {
        if ($txId->binary()->size()->bits() !== 256) {
            throw new \InvalidArgumentException('Transaction hash must be 256 bit');
        }

        $params = [
            "transaction" => $txId->hexits(false),
            "binary" => false
        ];

        $req = $this->request("tx", $params);
        $txInfo = Transaction::ConstructPerType($req->result()->array(), true);
        return $txInfo;
    }

    /**
     * @param Base16|null $seed
     * @param string $keyType
     * @return WalletPropose
     * @throws APIQueryException
     */
    public function walletPropose(?Base16 $seed = null, string $keyType = "secp256k1"): WalletPropose
    {
        if (!in_array($keyType, Validator::KEY_TYPES)) {
            throw new \OutOfBoundsException('Invalid key type');
        }

        $params = [
            "key_type" => $keyType
        ];

        if ($seed) {
            $params["seed_hex"] = $seed->hexits(false);
        }

        return $this->_walletPropose($params);
    }

    /**
     * @param string $passphrase
     * @param string $keyType
     * @return WalletPropose
     * @throws APIQueryException
     */
    public function walletProposeWithPassphrase(string $passphrase, string $keyType = "secp256k1"): WalletPropose
    {
        if (!in_array($keyType, Validator::KEY_TYPES)) {
            throw new \OutOfBoundsException('Invalid key type');
        }

        $params = [
            "key_type" => $keyType,
            "passphrase" => $passphrase
        ];

        return $this->_walletPropose($params);
    }

    /**
     * @param array $params
     * @return WalletPropose
     * @throws APIQueryException
     */
    private function _walletPropose(array $params): WalletPropose
    {
        $req = $this->request("wallet_propose", $params);
        $proposedWallet = new WalletPropose();
        $proposedWallet->mapResultToObject($req->result()->array());
        $proposedWallet->masterSeedHex = new Base16($proposedWallet->masterSeedHex);
        $proposedWallet->publicKeyHex = new Base16($proposedWallet->publicKeyHex);

        return $proposedWallet;
    }


    /**
     * @param string $command
     * @param array|null $params
     * @param bool $requireResultObj
     * @param bool $exceptionOnFail
     * @param string $httpMethod
     * @return APIQueryResult
     * @throws APIQueryException
     */
    public function request(string $command, ?array $params = null, bool $requireResultObj = true, bool $exceptionOnFail = true, string $httpMethod = 'POST'): APIQueryResult
    {
        try {
            $url = sprintf('%s://%s:%d', $this->https ? "https" : "http", $this->host, $this->port);
            $req = new Request($httpMethod, $url);
            $req->json(true, false);
            $req->payload([
                "method" => $command,
                "params" => [
                    $params ?? []
                ]
            ], true);

            /** @var Response|JSONResponse $res */
            $res = $req->send();
        } catch (HttpClientException $e) {
            throw new ConnectionException(sprintf('[%s][%s] %s', get_class($e), $e->getCode(), $e->getMessage()));
        }

        if (!$res instanceof JSONResponse) {
            try {
                $message = $res->body();
                if (is_string($message) && strlen($message) > 0 && strlen($message) < 128) {
                    throw new APIQueryException(
                        sprintf('[%d] Rippled API did not send JSON: %s', $res->code(), $message)
                    );
                }

                throw new APIQueryException("Rippled API did not send JSON body");
            } catch (APIQueryException $e) {
                if ($exceptionOnFail) {
                    throw $e;
                }
            }
        }

        try {
            $apiResult = new Result($res);
        } catch (ResponseParseException $e) {
            if ($exceptionOnFail) {
                throw $e;
            }
        }

        $apiQueryResult = new APIQueryResult($res, $apiResult ?? null);
        if ($exceptionOnFail) {
            if (isset($apiResult)) {
                if (!$apiResult->isSuccess()) {
                    $errorMessage = $apiResult->error();
                    if ($errorMessage) {
                        throw new APIQueryException(
                            sprintf('API command "%s" status "error"; %s', $command, $errorMessage),
                            APIQueryException::SIGNALS[$errorMessage] ?? 0
                        );
                    }

                    throw new APIQueryException(sprintf('API command "%s" status "error"', $command));
                }
            }
        }

        if (!isset($apiResult) && $requireResultObj) {
            throw new APIQueryException(sprintf('API command "%s" no result object', $command));
        }

        return $apiQueryResult;
    }
}