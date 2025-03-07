<?php
declare(strict_types=1);

namespace framework\clarity\Http;

use framework\clarity\Http\interfaces\MessageInterface;
use framework\clarity\Http\interfaces\StreamInterface;

class Message implements MessageInterface
{
    protected string $protocolVersion;
    protected array $headers;
    protected StreamInterface $body;

    /**
     * @param string $protocolVersion
     * @param array $headers
     * @param StreamInterface|null $body
     */
    public function __construct(
        ?StreamInterface $body = null,
        string $protocolVersion = '1.1',
        array $headers = [],
    ) {
        $this->headers = $headers ?: (function_exists('getallheaders') ? getallheaders() : []);

        $this->headers = array_change_key_case($this->headers, CASE_LOWER);

        $this->protocolVersion = $protocolVersion;

        $this->body = $body ?? new Stream(fopen('php://temp', 'r+'));
    }

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @param string $version
     * @return $this
     */
    public function withProtocolVersion(string $version): Message
    {
        $messageInstance  = clone $this;

        $messageInstance->protocolVersion = $version;

        return $messageInstance;
    }

    /**
     * @param string $header
     * @return bool
     */
    public function hasHeader(string $header): bool
    {
        $header = strtolower($header);

        return array_key_exists($header, $this->headers) === true;
    }

    /**
     * @param string $header
     * @return string
     */
    public function getHeaderLine(string $header): string
    {
        $header = strtolower($header);

        if (isset($this->headers[$header]) === true) {
            if (is_array($this->headers[$header]) === true) {
                return implode(', ', $this->headers[$header]);
            }

            return $this->headers[$header];
        }

        return '';
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param string $header
     * @param string $value
     * @return void
     */
    public function setHeader(string $header, string $value): void
    {
        $this->headers[strtolower($header)] = $value;
    }

    /**
     * @param string $header
     * @param string|array $value
     * @return $this
     */
    public function withHeader(string $header, string|array $value): Message
    {
        $messageInstance = clone $this;

        $messageInstance->headers[strtolower($header)] = $value;

        return $messageInstance;
    }

    /**
     * @param string $header
     * @param string|array $value
     * @return $this
     */
    public function withAddedHeader(string $header, string|array $value): Message
    {
        $messageInstance = clone $this;

        $messageInstance->headers[strtolower($header)][] = $value;

        return $messageInstance;
    }

    /**
     * @param string $header
     * @return $this
     */
    public function withoutHeader(string $header): Message
    {
        $header = strtolower($header);

        $messageInstance = clone $this;

        if (isset($messageInstance->headers[$header]) === true) {
            unset($messageInstance->headers[$header]);
        };

        return $messageInstance;
    }

    /**
     * @param string $header
     * @return string|null
     */
    public function getHeader(string $header): ?string
    {
        return $this->headers[strtolower($header)] ?? null;
    }

    /**
     * @param StreamInterface $body
     * @return void
     */
    public function setBody(StreamInterface $body): void
    {
        $this->body = $body;
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @param StreamInterface $body
     * @return $this
     */
    public function withBody(StreamInterface $body): Message
    {
        $messageInstance = clone $this;

        $messageInstance->body = $body;

        return $messageInstance;
    }
}