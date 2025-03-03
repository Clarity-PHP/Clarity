<?php

declare(strict_types=1);

namespace framework\clarity\Http\router\exceptions;

class HttpInternalServerErrorException extends HttpException
{
    public function __construct(string $message = 'The server encountered an unexpected condition that prevented it from fulfilling the request.')
    {
        parent::__construct($message, 500);
    }
}