<?php

declare(strict_types=1);

namespace framework\clarity\Http;

use framework\clarity\Http\enum\ReasonPhraseEnum;
use framework\clarity\Http\interfaces\ResponseInterface;
use framework\clarity\Http\interfaces\StreamInterface;

class Response extends Message implements ResponseInterface
{
    public const BUFFER_SIZE_LOW = 1024;
    public const BUFFER_SIZE_MEDIUM = 4096;
    public const BUFFER_SIZE_HIGH = 8192;
    private int $statusCode;
    private string $reasonPhrase;
    private int $readBlockSize;

    /**
     * @param StreamInterface|null $body
     */
    public function __construct(?StreamInterface $body = null)
    {
        parent::__construct(body: $body);

        if ($this->body->getSize() === 0) {
            $this->setHeader('Content-Length', '0');
        }

        $this->statusCode = http_response_code();

        $this->reasonPhrase = ReasonPhraseEnum::getReasonPhrase($this->statusCode);

        $this->readBlockSize = self::BUFFER_SIZE_LOW;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return $this
     */
    public function withStatus(int $code, string $reasonPhrase = ''): Response
    {
        $newResponse = clone $this;

        $newResponse->statusCode = $code;

        return $newResponse;
    }

    /**
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * @param int $statusCode
     * @return void
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @param string $reasonPhrase
     * @return void
     */
    public function setReasonPhrase(string $reasonPhrase): void
    {
        $this->reasonPhrase = $reasonPhrase;
    }

    /**
     * @return int
     */
    public function getReadBlockSize(): int
    {
        return $this->readBlockSize;
    }

    /**
     * @param int $readBlockSize
     * @return void
     */
    public function setReadBlockSize(int $readBlockSize): void
    {
        $this->readBlockSize = $readBlockSize;
    }

    /**
     * @inheritDoc
     */
    public function send(): void
    {
        $this->sendHeaders();

        $this->sendBody();

        if (function_exists('fastcgi_finish_request') === true) {
            fastcgi_finish_request();

            return;
        }

        flush();
    }

    /**
     * @return void
     */
    private function sendHeaders(): void
    {
        if (headers_sent() === true) {
            return;
        }

        header(sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $this->statusCode, $this->reasonPhrase));

        foreach ($this->headers as $name => $value) {
            header("$name: $value", false);
        }
    }

    /**
     * @return void
     */
    private function sendBody(): void
    {
        // Получаем размер тела
        $bodySize = $this->body->getSize();


        if ($bodySize === 0) {
            return;
        }

        $this->withHeader('Content-Length', (string)$bodySize);

        if ($this->body->getSize() === 0) {
            return;
        }

        $this->body->rewind();

        while ($this->body->eof() === false) {
            echo $this->body->read($this->readBlockSize);
        }
    }
}