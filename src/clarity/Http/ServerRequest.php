<?php

declare(strict_types=1);

namespace framework\clarity\Http;

use framework\clarity\Http\interfaces\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    private array $serverParams;
    private array $cookieParams;
    private array $queryParams;
    private array $uploadedFiles;
    private mixed $parsedBody;
    private array $attributes;

    /**
     * @param array $uploadedFiles
     * @param array $attributes
     */
    public function __construct(array $uploadedFiles = [], array $attributes = [])
    {
        parent::__construct();

        $this->serverParams = $_SERVER;

        $this->cookieParams = $_COOKIE;

        $this->queryParams = $_GET ?? [];

        $this->uploadedFiles = $uploadedFiles;

        $this->parsedBody = $this->parseRequestBody();

        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * @return array
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * @param array $cookies
     * @return ServerRequestInterface
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $newServerRequest = clone $this;

        $newServerRequest->cookieParams = $cookies;

        return $newServerRequest;
    }

    /**
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @param array $query
     * @return ServerRequestInterface
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $newServerRequest = clone $this;

        $newServerRequest->queryParams = $query;

        return $newServerRequest;
    }

    /**
     * @return array
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * @param array $uploadedFiles
     * @return ServerRequestInterface
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $newServerRequest = clone $this;

        $newServerRequest->uploadedFiles = $uploadedFiles;

        return $newServerRequest;
    }

    /**
     * @return array|object|null
     */
    public function getParsedBody(): null|array|object
    {
        return $this->parsedBody;
    }

    /**
     * @param array|object|null $data
     * @return ServerRequestInterface
     */
    public function withParsedBody(null|array|object $data): ServerRequestInterface
    {
        $newServerRequest = clone $this;

        $newServerRequest->parsedBody = $data;

        return $newServerRequest;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param null $default
     * @return ServerRequestInterface|null
     */
    public function getAttribute(string $name, $default = null): ?ServerRequestInterface
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @param string $name
     * @param $value
     * @return ServerRequestInterface
     */
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $new = clone $this;

        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * @param string $name
     * @return ServerRequestInterface
     */
    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $new = clone $this;

        unset($new->attributes[$name]);

        return $new;
    }

    /**
     * @return array|null
     */
    private function parseRequestBody(): ?array
    {
        $contentType = $this->getHeaderLine('Content-Type');

        $method = $this->getMethod();

        if (str_contains($contentType, 'application/json')) {
            $body = file_get_contents('php://input');

            if ($body === false || $body === '') {
                $body = $this->getBody()->getContents();
            }

            return json_decode($body, true);
        }

        if ($method === 'POST') {
            return $_POST;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isAjax(): bool
    {
        $acceptHeader = $this->getHeaderLine('Accept');

        $requestedWith = $this->getHeaderLine('X-Requested-With');

        return (
            (empty($requestedWith) === false && strtolower($requestedWith) === 'XMLHttpRequest') ||
            (str_contains($acceptHeader, 'application/json') || $acceptHeader === '*/*')
        );
    }
}