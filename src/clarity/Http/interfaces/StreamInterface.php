<?php

declare(strict_types=1);

namespace framework\clarity\Http\interfaces;

use RuntimeException;

interface StreamInterface
{
    public function __toString(): string;

    /**
     * @return void
     */
    public function close(): void;

    /**
     * @return mixed
     */
    public function detach(): mixed;

    /**
     * @return int|null
     */
    public function getSize(): int|null;

    /**
     * @return int
     * @throws RuntimeException
     */
    public function tell(): int;

    /**
     * @return bool
     */
    public function eof(): bool;

    /**
     * @return bool
     */
    public function isSeekable(): bool;

    /**
     * @param int $offset
     * @param int $whence
     * @return void
     */
    public function seek(int $offset, int $whence = SEEK_SET): void;

    /**
     * @return void
     * @throws RuntimeException
     */
    public function rewind(): void;

    /**
     * @return bool
     */
    public function isWritable(): bool;

    /**
     * @param string $string
     * @return int
     */
    public function write(string $string): int;

    /**
     * @return bool
     */
    public function isReadable(): bool;

    /**
     * @param int $length
     * @return string
     */
    public function read(int $length): string;

    /**
     * @return string
     * @throws RuntimeException
     */
    public function getContents(): string;

    /**
     * @param string $key
     * @return array
     */
    public function getMetadata(string $key): mixed;
}