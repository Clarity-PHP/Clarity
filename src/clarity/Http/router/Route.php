<?php

declare(strict_types=1);

namespace framework\clarity\Http\router;

use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Http\router\interfaces\RouteInterface;
use framework\clarity\http\router\interfaces\MiddlewareAssignable;
use framework\clarity\http\router\interfaces\MiddlewareInterface;
use InvalidArgumentException;

class Route implements RouteInterface, MiddlewareAssignable
{
    public array $routeMiddlewares = [];

    public function __construct(
        private readonly ContainerInterface $container,
        public string $method,
        public string $path,
        public mixed  $handler,
        public mixed  $action,
        public array  $params,
    ) {}


    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getHandler(): string
    {
        return $this->handler;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function addMiddleware(callable|string $middleware): MiddlewareAssignable
    {
        if (is_string($middleware) === true) {
            $middleware = $this->container->get($middleware);
        }

        if ($middleware instanceof MiddlewareInterface === false) {
            throw new InvalidArgumentException('Некорректный формат midlleware, объект должен быть имплементировать MiddlewareInterface');
        }

        $this->routeMiddlewares[] = $middleware;

        return $this;
    }
}