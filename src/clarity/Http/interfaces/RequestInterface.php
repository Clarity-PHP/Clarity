<?php

declare(strict_types=1);

namespace framework\clarity\Http\interfaces;

interface RequestInterface extends MessageInterface
{
    /**
     * @return string
     */
    public function getRequestTarget(): string;

    /**
     * @param string $requestTarget
     * @return framework\clarity\Http\interfaces\RequestInterface
     */
    public function withRequestTarget(mixed $requestTarget): framework\clarity\Http\interfaces\RequestInterface;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param string $method
     * @return framework\clarity\Http\interfaces\RequestInterface
     */
    public function withMethod(string $method): framework\clarity\Http\interfaces\RequestInterface;

    /**
     * @return UriInterface
     */
    public function getUri(): UriInterface;

    /**
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return framework\clarity\Http\interfaces\RequestInterface
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): framework\clarity\Http\interfaces\RequestInterface;
}
