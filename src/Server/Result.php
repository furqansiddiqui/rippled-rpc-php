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

use FurqanSiddiqui\Rippled\Exception\ResponseParseException;
use HttpClient\Response\JSONResponse;

/**
 * Class Result
 * @package FurqanSiddiqui\Rippled\Server
 */
class Result
{
    /** @var array|null */
    public $result;

    /**
     * Result constructor.
     * @param JSONResponse $res
     * @throws ResponseParseException
     */
    public function __construct(JSONResponse $res)
    {
        if (!$res->has("result")) {
            throw new ResponseParseException('Required "result" object missing from response');
        }

        $result = $res->get("result");
        if (!is_array($result)) {
            throw new ResponseParseException(
                sprintf('Expected "result" prop as object, got "%s"', gettext($result))
            );
        }

        if (!array_key_exists("status", $result)) {
            throw new ResponseParseException('Required prop "status" missing from "result" object');
        }

        $this->result = $result;
    }

    /**
     * @param string $prop
     * @return bool
     */
    public function has(string $prop): bool
    {
        return array_key_exists($prop, $this->result);
    }

    /**
     * @param string $prop
     * @return mixed|null
     */
    public function get(string $prop)
    {
        return $this->result[$prop] ?? null;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->result;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return isset($this->result["status"]) && $this->result["status"] === "success" ? true : false;
    }

    /**
     * @return string|null
     * @throws ResponseParseException
     */
    public function error(): ?string
    {
        $error = $this->result["error"] ?? null;
        if (!is_null($error) && !is_string($error)) {
            throw new ResponseParseException(
                sprintf('Value for result obj "error" must be of type string, got %s', gettype($error))
            );
        }

        return $error;
    }
}