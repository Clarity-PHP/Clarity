<?php

declare(strict_types=1);

namespace framework\clarity\Http\router\exceptions;

class HttpBadRequestException extends HttpException
{
    public function __construct(string $message = 'Bad Request')
    {
        parent::__construct($message, 400);
    }
}