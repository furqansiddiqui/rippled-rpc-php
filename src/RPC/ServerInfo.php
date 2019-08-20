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

/**
 * Class ServerInfo
 * @package FurqanSiddiqui\Rippled\RPC
 */
class ServerInfo extends AbstractResultModel
{
    /** @var null|bool */
    public $amendmentBlocked;
    /** @var string */
    public $buildVersion;
    /** @var array|null */
    public $closedLedger;
    /** @var string|null */
    public $completeLedgers;
    /** @var int|null */
    public $peers;
    /** @var string|null */
    public $serverState;
    /** @var string */
    public $pubKeyNode;

    /**
     * @param ObjectMapper $objectMapper
     */
    public function objectMapperProps(ObjectMapper $objectMapper): void
    {
        $objectMapper->prop("amendmentBlocked")->nullable()->dataTypes("boolean");
        $objectMapper->prop("buildVersion")->dataTypes("string");
        $objectMapper->prop("closedLedger")->dataTypes("array")->nullable();
        $objectMapper->prop("completeLedgers")->dataTypes("string")->nullable();
        $objectMapper->prop("peers")->dataTypes("integer")->nullable();
        $objectMapper->prop("serverState")->dataTypes("string")->nullable();
        $objectMapper->prop("pubKeyNode")->dataTypes("string");
    }
}