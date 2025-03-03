<?php

declare(strict_types=1);

namespace framework\clarity\Http\interfaces;

interface ResponseInterface extends MessageInterface
{
    /**
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return framework\clarity\Http\interfaces\ResponseInterface
     */
    public function withStatus(int $code, string $reasonPhrase = ''): framework\clarity\Http\interfaces\ResponseInterface;

    /**
     * @return string
     */
    public function getReasonPhrase(): string;

    /**
     * @return void
     */
    public function send(): void;
}
