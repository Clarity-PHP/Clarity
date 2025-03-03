<?php

declare(strict_types=1);

namespace framework\clarity\Http;

use framework\clarity\Http\interfaces\RequestInterface;
use framework\clarity\Http\interfaces\UriInterface;

class Request extends Message implements RequestInterface
{
    private string $method;
    private UriInterface $uri;
    private string|null $requestTarget = null;

    /**
     * @param $requestTarget
     */
    public function __construct($requestTarget = '/')
    {
        parent::__construct();

        $this->method = $_SERVER['REQUEST_METHOD'];

        $this->requestTarget = $requestTarget;

        $this->uri = new Uri($_SERVER['REQUEST_URI']);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function withMethod(string $method): framework\clarity\Http\Request
    {
        $newRequest = clone $this;

        $newRequest->method = $method;

        return $newRequest;
    }

    /**
     * @return Uri
     */
    public function getUri(): Uri
    {
        return $this->uri;
    }

    /**
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return $this
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): framework\clarity\Http\Request
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if ($preserveHost === false) {
            if ($uri->getHost() !== '') {
                $clone->headers['Host'] = [$uri->getHost()];
            }
        }

        return $clone;
    }

    /**
     * @return string
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ($this->uri->getQuery()) {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target ?: '/';
    }

    /**
     * @param mixed $requestTarget
     * @return $this
     */
    public function withRequestTarget(mixed $requestTarget): framework\clarity\Http\Request
    {
        $newRequest = clone $this;

        $newRequest->requestTarget = $requestTarget;

        return $newRequest;
    }
}