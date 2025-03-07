<?php

declare(strict_types=1);

namespace framework\clarity\Http;

use framework\clarity\Http\interfaces\UriInterface;
use InvalidArgumentException;

class Uri implements UriInterface
{
    private string $scheme = '';
    private string $userInfo = '';
    private string $host = '';
    private ?int $port = null;
    private string $path = '';
    private string $query = '';
    private string $fragment = '';

    /**
     * @param string $uri
     */
    public function __construct(
        string $uri
    )
    {
        if ($uri !== '') {
            $parsed = parse_url($uri);

            if ($parsed === false) {
                throw new InvalidArgumentException("Invalid URI: $uri");
            }

            $this->scheme = $parsed['scheme'] ?? '';
            $this->userInfo = $parsed['user'] ?? '';
            $this->host = $parsed['host'] ?? '';
            $this->port = $parsed['port'] ?? null;
            $this->path = $parsed['path'] ?? '';
            $this->query = $parsed['query'] ?? '';
            $this->fragment = $parsed['fragment'] ?? '';
        }
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return strtolower($this->scheme);
    }

    /**
     * @return string
     */
    public function getAuthority(): string
    {
        $authority = '';
        if ((bool)$this->userInfo === true) {
            $authority .= $this->userInfo . '@';
        }

        $authority .= $this->host;

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }

    /**
     * @return string
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return strtolower($this->host);
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @param string $scheme
     * @return $this
     */
    public function withScheme(string $scheme): Uri
    {
        $newUri = clone $this;

        $newUri->scheme = strtolower($scheme);

        return $newUri;
    }


    /**
     * @param string $user
     * @param string $password
     * @return $this
     */
    public function withUserInfo(string $user, string $password): Uri
    {
        $newUri = clone $this;

        $newUri->userInfo = $password ? $user . ':' . $password : $user;

        return $newUri;
    }


    /**
     * @param string $host
     * @return $this
     */
    public function withHost(string $host): Uri
    {
        $newUri = clone $this;

        $newUri->host = strtolower($host);

        return $newUri;
    }

    /**
     * @param int $port
     * @return $this
     */
    public function withPort(int $port): Uri
    {
        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException("Invalid port number: $port");
        }

        $newUri = clone $this;

        $newUri->port = $port;

        return $newUri;
    }

    /**
     * @param $path
     * @return $this
     */
    public function withPath($path): Uri
    {
        $newUri = clone $this;

        $newUri->path = $path;

        return $newUri;
    }

    /**
     * @param $query
     * @return $this
     */
    public function withQuery($query): Uri
    {
        $newUri = clone $this;

        $newUri->query = $query;

        return $newUri;
    }

    /**
     * @param string $fragment
     * @return $this
     */
    public function withFragment(string $fragment): Uri
    {
        $newUri = clone $this;

        $newUri->fragment = $fragment;

        return $newUri;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $uri = '';

        if ((bool)$this->scheme === true) {
            $uri .= $this->scheme . ':';
        }

        if ((bool)$this->host === true) {
            $uri .= '//';
            $uri .= $this->getAuthority();
        }

        $uri .= $this->path;

        if ((bool)$this->query === true) {
            $uri .= '?' . $this->query;
        }

        if ((bool)$this->fragment === true) {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }
}
