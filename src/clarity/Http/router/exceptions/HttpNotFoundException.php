<?php

declare(strict_types=1);

namespace framework\clarity\Http\router\exceptions;

use Throwable;

class HttpNotFoundException extends HttpException
{
    public function __construct(string $message = 'The server can not find the requested resource.',  ?Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}