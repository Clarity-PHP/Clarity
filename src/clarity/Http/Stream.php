<?php

declare(strict_types=1);

namespace framework\clarity\Http;

use framework\clarity\Http\interfaces\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    private mixed $stream;
    private bool $isSeekable = true;
    private bool $isReadable = true;
    private bool $isWritable = true;

    /**
     * @param mixed $stream
     */
    public function __construct(mixed $stream)
    {
        if ($stream === null) {
            $stream = fopen('php://temp', 'rw+');
        }

        if (is_resource($stream) === false) {
            throw new RuntimeException("Invalid stream resource provided.");
        }

        $this->stream = $stream;

        $this->isSeekable = stream_get_meta_data($stream)['seekable'];
    }

    public function __toString(): string
    {
        $this->seek(0);

        $content = stream_get_contents($this->stream);

        return $content === false ? '' : $content;
    }

    /**
     * @return void
     */
    public function close(): void
    {
        if (is_resource($this->stream) === true) {
            fclose($this->stream);
            $this->stream = null;
        }
    }

    /**
     * @return mixed
     */
    public function detach(): mixed
    {
        $stream = $this->stream;
        $this->stream = null;

        return $stream;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        if ($this->stream === null) {
            return null;
        }

        $stats = fstat($this->stream);

        return $stats['size'] ?? null;
    }

    /**
     * @return int
     */
    public function tell(): int
    {
        if ($this->stream === null) {
            throw new RuntimeException("Stream is not open.");
        }

        $position = ftell($this->stream);
        if ($position === false) {
            throw new RuntimeException("Unable to determine stream position.");
        }

        return $position;
    }

    /**
     * @return bool
     */
    public function eof(): bool
    {
        return feof($this->stream);
    }

    /**
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->isSeekable;
    }

    /**
     * @param $offset
     * @param $whence
     * @return void
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if ($this->isSeekable === false) {
            throw new RuntimeException("Stream is not seekable.");
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException("Failed to seek to position $offset.");
        }
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->isWritable;
    }

    /**
     * @param $string
     * @return int
     */
    public function write($string): int
    {
        if ($this->isWritable === false) {
            throw new RuntimeException("Stream is not writable.");
        }

        $bytesWritten = fwrite($this->stream, $string);
        if ($bytesWritten === false) {
            throw new RuntimeException("Failed to write to stream.");
        }

        return $bytesWritten;
    }

    /**
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->isReadable;
    }

    /**
     * @param $length
     * @return string
     */
    public function read($length): string
    {
        if ($this->isReadable === false) {
            throw new RuntimeException("Stream is not readable.");
        }

        $data = fread($this->stream, $length);
        if ($data === false) {
            throw new RuntimeException("Failed to read from stream.");
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        if ($this->isReadable === false) {
            throw new RuntimeException("Stream is not readable.");
        }

        return stream_get_contents($this->stream);
    }

    /**
     * @param $key
     * @return mixed|nullå
     */
    public function getMetadata($key = null): array|null
    {
        return stream_get_meta_data($this->stream)[$key] ?? null;
    }

    /**
     * @param bool $isWritable
     * @return void
     */
    public function setIsWritable(bool $isWritable): void
    {
        $this->isWritable = $isWritable;
    }

    /**
     * @param bool $isReadable
     * @return void
     */
    public function setIsReadable(bool $isReadable): void
    {
        $this->isReadable = $isReadable;
    }
}