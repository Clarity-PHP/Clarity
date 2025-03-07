<?php

declare(strict_types=1);

namespace framework\clarity\Http\interfaces;

use framework\clarity\Http\Uri;
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
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withScheme(string $scheme): UriInterface;

    /**
     * @param string $user
     * @param string $password
     * @return UriInterface
     */
    public function withUserInfo(string $user, string $password): UriInterface;

    /**
     * @param string $host
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withHost(string $host): UriInterface;

    /**
     * @param int $port
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withPort(int $port): UriInterface;

    /**
     * @param string $path
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withPath(string $path): UriInterface;

    /**
     * @param string $query
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withQuery(string $query): UriInterface;

    /**
     * @param string $fragment
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withFragment(string $fragment): UriInterface;

    /**
     * @return string
     */
    public function __toString(): string;
}