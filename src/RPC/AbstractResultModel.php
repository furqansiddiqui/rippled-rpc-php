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
use Comely\Utils\OOP\ObjectMapper\ObjectMapperInterface;
use Comely\Utils\OOP\OOP;
use FurqanSiddiqui\Rippled\Exception\ResponseParseException;
use FurqanSiddiqui\Rippled\Server\Result;

/**
 * Class AbstractResultModel
 * @package FurqanSiddiqui\Rippled\RPC
 */
abstract class AbstractResultModel implements ObjectMapperInterface
{
    /**
     * @param Result $res
     * @return ObjectMapperInterface
     * @throws ResponseParseException
     */
    public function mapResultToObject(Result $res): ObjectMapperInterface
    {
        try {
            $objectMapper = new ObjectMapper($this);
            return $objectMapper->mapCaseConversion(true)
                ->map($res->array());
        } catch (\Exception $e) {
            throw new ResponseParseException(
                sprintf('[%s] %s', OOP::baseClassName(get_class($e)), $e->getMessage()),
                $e->getCode()
            );
        }
    }
}