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

use FurqanSiddiqui\Rippled\Exception\APIQueryException;
use FurqanSiddiqui\Rippled\Exception\ResponseParseException;
use FurqanSiddiqui\Rippled\Server\APIQueryResult;
use FurqanSiddiqui\Rippled\Server\Result;
use FurqanSiddiqui\Rippled\Server\SSL;
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
     * @param string $httpMethod
     * @param string $command
     * @param array|null $params
     * @param bool $exceptionOnFail
     * @return APIQueryResult
     * @throws APIQueryException
     * @throws ResponseParseException
     * @throws \HttpClient\Exception\HttpClientException
     * @throws \HttpClient\Exception\RequestException
     * @throws \HttpClient\Exception\ResponseException
     */
    private function request(string $httpMethod, string $command, ?array $params = null, bool $exceptionOnFail = true): APIQueryResult
    {
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
                            sprintf('API command "%s" status "error"; %s', $command, $errorMessage)
                        );
                    }

                    throw new APIQueryException(sprintf('API command "%s" status "error"', $command));
                }
            }
        }

        return $apiQueryResult;
    }
}