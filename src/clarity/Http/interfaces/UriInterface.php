<?php

declare(strict_types=1);

namespace framework\clarity\Http\interfaces;

use InvalidArgumentException;

interface UriInterface
{
    /**
     * @return string
     */
    public function getScheme(): string;

    /**
     * @return string
     */
    public function getAuthority(): string;

    /**
     * @return string
     */
    public function getUserInfo(): string;

    /**
     * @return string
     */
    public function getHost(): string;

    /**
     * @return int|null
     */
    public function getPort(): int|null;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string
     */
    public function getQuery(): string;

    /**
     * @return string
     */
    public function getFragment(): string;

    /**
     * @param string $scheme
     * @return framework\clarity\Http\interfaces\UriInterface
     * @throws InvalidArgumentException
     */
    public function withScheme(string $scheme): framework\clarity\Http\interfaces\UriInterface;

    /**
     * @param string $user
     * @param string $password
     * @return framework\clarity\Http\interfaces\UriInterface
     */
    public function withUserInfo(string $user, string $password): framework\clarity\Http\interfaces\UriInterface;

    /**
     * @param string $host
     * @return framework\clarity\Http\interfaces\UriInterface
     * @throws InvalidArgumentException
     */
    public function withHost(string $host): framework\clarity\Http\interfaces\UriInterface;

    /**
     * @param int $port
     * @return framework\clarity\Http\interfaces\UriInterface
     * @throws InvalidArgumentException
     */
    public function withPort(int $port): framework\clarity\Http\interfaces\UriInterface;

    /**
     * @param string $path
     * @return framework\clarity\Http\interfaces\UriInterface
     * @throws InvalidArgumentException
     */
    public function withPath(string $path): framework\clarity\Http\interfaces\UriInterface;

    /**
     * @param string $query
     * @return framework\clarity\Http\interfaces\UriInterface
     * @throws InvalidArgumentException
     */
    public function withQuery(string $query): framework\clarity\Http\interfaces\UriInterface;

    /**
     * @param string $fragment
     * @return framework\clarity\Http\interfaces\UriInterface
     * @throws InvalidArgumentException
     */
    public function withFragment(string $fragment): framework\clarity\Http\interfaces\UriInterface;

    /**
     * @return string
     */
    public function __toString(): string;
}