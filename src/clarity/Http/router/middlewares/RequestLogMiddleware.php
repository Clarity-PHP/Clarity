<?php

declare(strict_types=1);

namespace framework\clarity\Http\router\middlewares;

use framework\clarity\Http\interfaces\ResponseInterface;
use framework\clarity\Http\interfaces\ServerRequestInterface;
use framework\clarity\Http\router\interfaces\MiddlewareInterface;
use framework\clarity\Logger\interfaces\LoggerInterface;

class RequestLogMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function process(ServerRequestInterface $request, ResponseInterface $response, callable $next): void
    {
        $this->logger->debug('Выполнено обращение методом ' . $request->getMethod() . ' к энпдоинту ' . $request->getUri()->getPath());

        if ($next !== null) {
            $next($request, $response);
        }
    }
}