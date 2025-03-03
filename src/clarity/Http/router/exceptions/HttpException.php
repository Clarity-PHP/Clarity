<?php

declare(strict_types=1);

namespace framework\clarity\Http\router\exceptions;

use Exception;
use Throwable;

class HttpException extends Exception
{
    private int $statusCode;

    public function __construct(string $message, int $statusCode, ?Throwable $previous = null)
    {
        $this->statusCode = $statusCode;

        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}