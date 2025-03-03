<?php

declare(strict_types=1);

namespace framework\clarity\Http\interfaces;

use framework\clarity\Http\Message;
use InvalidArgumentException;

interface MessageInterface
{
    /**
     * @return string
     */
    public function getProtocolVersion(): string;

    /**
     * @param string $version
     * @return framework\clarity\Http\interfaces\MessageInterface
     */
    public function withProtocolVersion(string $version): framework\clarity\Http\interfaces\MessageInterface;

    /**
     * @return array
     */
    public function getHeaders(): array;

    /**
     * @param string $header
     * @return bool
     */
    public function hasHeader(string $header): bool;

    /**
     * @param string $header
     * @return string|null
     */
    public function getHeader(string $header): ?string;

    /**
     * @param string $header
     * @return string
     */
    public function getHeaderLine(string $header): string;

    /**
     * @param string $header
     * @param string|array $value
     * @return framework\clarity\Http\interfaces\MessageInterface
     * @throws InvalidArgumentException
     */
    public function withHeader(string $header, string|array $value): framework\clarity\Http\interfaces\MessageInterface;

    /**
     * @param string $header
     * @param string|array $value
     * @return framework\clarity\Http\interfaces\MessageInterface
     * @throws InvalidArgumentException
     */
    public function withAddedHeader(string $header, string|array $value): framework\clarity\Http\interfaces\MessageInterface;

    /**
     * @param string $header
     * @return framework\clarity\Http\interfaces\MessageInterface
     */
    public function withoutHeader(string $header): framework\clarity\Http\interfaces\MessageInterface;

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface;

    /**
     * @param StreamInterface $body
     * @return Message
     */
    public function withBody(StreamInterface $body): framework\clarity\Http\interfaces\MessageInterface;
}