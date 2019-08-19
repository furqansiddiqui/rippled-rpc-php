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

use HttpClient\Request;
use HttpClient\Exception\SSLException;

/**
 * Class SSL
 * @package FurqanSiddiqui\Rippled\Server
 */
class SSL
{
    /** @var bool */
    private $verify;
    /** @var null|string */
    private $certPath;
    /** @var null|string */
    private $certPassword;
    /** @var null|string */
    private $privateKeyPath;
    /** @var null|string */
    private $privateKeyPassword;
    /** @var null|string */
    private $certAuthorityPath;

    /**
     * SSL constructor.
     */
    public function __construct()
    {
        // Make sure cUrl can work with SSL
        if (!(curl_version()["features"] & CURL_VERSION_SSL)) {
            throw new \RuntimeException('SSL support is unavailable in your cURL build');
        }

        $this->verify = true;
    }

    /**
     * @param bool $bool
     * @return SSL
     */
    public function verify(bool $bool): self
    {
        $this->verify = $bool;
        return $this;
    }

    /**
     * @param string $file
     * @param null|string $password
     * @return SSL
     * @throws SSLException
     */
    public function certificate(string $file, ?string $password = null): self
    {
        $path = realpath($file);
        if (!$path || !is_readable($path) || !is_file($path)) {
            throw new SSLException(sprintf('SSL certificate "%s" not found or not readable', basename($file)));
        }

        $this->certPath = $path;
        $this->certPassword = $password;
        return $this;
    }

    /**
     * @param string $file
     * @param null|string $password
     * @return SSL
     * @throws SSLException
     */
    public function privateKey(string $file, ?string $password = null): self
    {
        $path = realpath($file);
        if (!$path || !is_readable($path) || !is_file($path)) {
            throw new SSLException(sprintf('SSL private key "%s" not found or not readable', basename($file)));
        }

        $this->privateKeyPath = $file;
        $this->privateKeyPassword = $password;
        return $this;
    }

    /**
     * @param string $path
     * @return SSL
     * @throws SSLException
     */
    public function ca(string $path): self
    {
        $path = realpath($path);
        if (!$path || !is_readable($path)) {
            throw new SSLException('Path to CA certificate(s) is invalid or not readable');
        }

        $this->certAuthorityPath = $path;
        return $this;
    }

    /**
     * @param string $path
     * @return SSL
     * @throws SSLException
     */
    public function certificateAuthority(string $path): self
    {
        return $this->ca($path);
    }

    /**
     * @param $method
     * @param $args
     * @throws SSLException
     */
    public function __call($method, $args)
    {
        switch ($method) {
            case "register":
                $this->register($args[0] ?? null);
                return;
        }

        throw new \DomainException(sprintf('Cannot call inaccessible method "%s"', $method));
    }

    /**
     * @param Request $req
     * @throws SSLException
     */
    private function register(Request $req): void
    {
        $req->ssl()->verify($this->verify);
        if ($this->certPassword) {
            $req->ssl()->certificate($this->certPath, $this->certPassword);
        }

        if ($this->privateKeyPath) {
            $req->ssl()->privateKey($this->privateKeyPath, $this->privateKeyPassword);
        }

        if ($this->certAuthorityPath) {
            $req->ssl()->ca($this->certAuthorityPath);
        }
    }
}