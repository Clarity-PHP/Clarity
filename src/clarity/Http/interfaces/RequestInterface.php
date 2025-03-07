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
     * @return RequestInterface
     */
    public function withRequestTarget(mixed $requestTarget): RequestInterface;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param string $method
     * @return RequestInterface
     */
    public function withMethod(string $method): RequestInterface;

    /**
     * @return UriInterface
     */
    public function getUri(): UriInterface;

    /**
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return RequestInterface
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface;
}
