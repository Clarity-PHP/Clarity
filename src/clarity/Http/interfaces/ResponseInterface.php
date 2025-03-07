<?php

declare(strict_types=1);

namespace framework\clarity\Http\interfaces;

use framework\clarity\Http\Response;

interface ResponseInterface extends MessageInterface
{
    /**
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return ResponseInterface
     */
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface;

    /**
     * @return string
     */
    public function getReasonPhrase(): string;

    /**
     * @return void
     */
    public function send(): void;
}
