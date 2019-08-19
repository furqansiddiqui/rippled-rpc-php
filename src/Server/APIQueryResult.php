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

namespace FurqanSiddiqui\Rippled\Server;

use HttpClient\Response\HttpClientResponse;

/**
 * Class APIQueryResult
 * @package FurqanSiddiqui\Rippled\Server
 */
class APIQueryResult
{
    /** @var HttpClientResponse */
    private $httpQuery;
    /** @var Result */
    private $resultObj;

    /**
     * APIQueryResult constructor.
     * @param HttpClientResponse $res
     * @param Result $result
     */
    public function __construct(HttpClientResponse $res, ?Result $result = null)
    {
        $this->httpQuery = $res;
        $this->resultObj = $result;
    }

    /**
     * @return HttpClientResponse
     */
    public function query(): HttpClientResponse
    {
        return $this->httpQuery;
    }

    /**
     * @return Result|null
     */
    public function result(): ?Result
    {
        return $this->resultObj;
    }
}