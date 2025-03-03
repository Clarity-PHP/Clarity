<?php

declare(strict_types=1);

namespace framework\clarity\Http\router\exceptions;

class HttpForbiddenException extends HttpException
{
    public function __construct(string $message = 'The server could not understand the request due to invalid syntax.')
    {
        parent::__construct($message, 403);
    }
}