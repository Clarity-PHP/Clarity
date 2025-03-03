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
     * @return framework\clarity\Http\interfaces\ServerRequestInterface
     */
    public function withCookieParams(array $cookies): framework\clarity\Http\interfaces\ServerRequestInterface;

    /**
     * @return array
     */
    public function getQueryParams(): array;

    /**
     * @param array $query
     * @return framework\clarity\Http\interfaces\ServerRequestInterface
     */
    public function withQueryParams(array $query): framework\clarity\Http\interfaces\ServerRequestInterface;

    /**
     * @return array
     */
    public function getUploadedFiles(): array;

    /**
     * @param array $uploadedFiles
     * @return framework\clarity\Http\interfaces\ServerRequestInterface
     */
    public function withUploadedFiles(array $uploadedFiles): framework\clarity\Http\interfaces\ServerRequestInterface;

    /**
     * @return array|object|null
     */
    public function getParsedBody(): null|array|object;

    /**
     * @param array|object|null $data
     * @return framework\clarity\Http\interfaces\ServerRequestInterface
     */
    public function withParsedBody(null|array|object $data): framework\clarity\Http\interfaces\ServerRequestInterface;

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @param string $name
     * @param mixed|null $default
     * @return framework\clarity\Http\interfaces\ServerRequestInterface
     */
    public function getAttribute(string $name, mixed $default = null): ?framework\clarity\Http\interfaces\ServerRequestInterface;

    /**
     * @param string $name
     * @param mixed $value
     * @return framework\clarity\Http\interfaces\ServerRequestInterface
     */
    public function withAttribute(string $name, mixed $value): framework\clarity\Http\interfaces\ServerRequestInterface;

    /**
     * @param string $name
     * @return framework\clarity\Http\interfaces\ServerRequestInterface
     */
    public function withoutAttribute(string $name): framework\clarity\Http\interfaces\ServerRequestInterface;

    /**
     * @return bool
     */
    public function isAjax(): bool;
}