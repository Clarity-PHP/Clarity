<?php

declare(strict_types=1);

namespace framework\clarity\Http\router\interfaces;

use framework\clarity\Http\interfaces\ResponseInterface;
use framework\clarity\Http\interfaces\ServerRequestInterface;

interface MiddlewareInterface
{
    /**
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface $response
     * @param  callable $next
     * @return void
     */
    public function process(
        framework\clarity\Http\interfaces\ServerRequestInterface $request, framework\clarity\Http\interfaces\ResponseInterface $response, callable $next): void;
}