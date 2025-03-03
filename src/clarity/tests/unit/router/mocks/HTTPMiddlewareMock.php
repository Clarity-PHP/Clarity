<?php

declare(strict_types=1);

namespace framework\clarity\tests\unit\router\mocks;

use framework\clarity\Http\interfaces\ResponseInterface;
use framework\clarity\Http\interfaces\ServerRequestInterface;
use framework\clarity\Http\router\interfaces\MiddlewareInterface;

class HTTPMiddlewareMock implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, ResponseInterface $response, callable $next): void
    {
    }
}