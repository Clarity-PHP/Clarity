<?php

declare(strict_types=1);

namespace framework\clarity\Http\interfaces;

interface ServerRequestInterface extends RequestInterface
{
    /**
     * @return array
     */
    public function getServerParams(): array;

    /**
     * @return array
     */
    public function getCookieParams(): array;

    /**
     * @param array $cookies
     * @return ServerRequestInterface
     */
    public function withCookieParams(array $cookies): ServerRequestInterface;

    /**
     * @return array
     */
    public function getQueryParams(): array;

    /**
     * @param array $query
     * @return ServerRequestInterface
     */
    public function withQueryParams(array $query): ServerRequestInterface;

    /**
     * @return array
     */
    public function getUploadedFiles(): array;

    /**
     * @param array $uploadedFiles
     * @return ServerRequestInterface
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface;

    /**
     * @return array|object|null
     */
    public function getParsedBody(): null|array|object;

    /**
     * @param array|object|null $data
     * @return ServerRequestInterface
     */
    public function withParsedBody(null|array|object $data): ServerRequestInterface;

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @param string $name
     * @param mixed|null $default
     * @return ServerRequestInterface
     */
    public function getAttribute(string $name, mixed $default = null): ?ServerRequestInterface;

    /**
     * @param string $name
     * @param mixed $value
     * @return ServerRequestInterface
     */
    public function withAttribute(string $name, mixed $value): ServerRequestInterface;

    /**
     * @param string $name
     * @return ServerRequestInterface
     */
    public function withoutAttribute(string $name): ServerRequestInterface;

    /**
     * @return bool
     */
    public function isAjax(): bool;
}