<?php

declare(strict_types=1);

namespace framework\clarity\Http\router;

use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Http\router\interfaces\HTTPRouterInterface;
use framework\clarity\Http\router\interfaces\MiddlewareAssignable;
use framework\clarity\Http\router\interfaces\MiddlewareInterface;
use InvalidArgumentException;

class RouteGroup implements MiddlewareAssignable
{
    private array $prefixes = [];
    public array $groupMiddlewares = [];


    public function __construct(
        private readonly HTTPRouterInterface $router,
        private readonly ContainerInterface $container,
        private array $prefix = []
    ) {
        $this->prefixes = $prefix;
    }

    /**
     * @param string $prefix
     * @return void
     */
    public function addPrefix(string $prefix): void
    {
        $this->prefixes[] = $prefix;
    }

    /**
     * @return array
     */
    public function getPrefixes(): array
    {
        return $this->prefixes;
    }

    /**
     * @param string $method
     * @param string $route
     * @param string|callable $handler
     * @return Route
     */
    public function addRoute(string $method, string $route, string|callable $handler): Route
    {
        $path = '/' . implode('/', $this->prefixes) . $route;

        return $this->router->add($method, $path, $handler);
    }

    /**
     * @param string $route
     * @param string|callable $handler
     * @return Route
     */
    public function get(string $route, string|callable $handler): Route
    {
        return $this->addRoute('GET', $route, $handler);
    }

    /**
     * @param string $route
     * @param string|callable $handler
     * @return Route
     */
    public function post(string $route, string|callable $handler): Route
    {
        return $this->addRoute('POST', $route, $handler);
    }

    /**
     * @param string $route
     * @param string|callable $handler
     * @return Route
     */
    public function put(string $route, string|callable $handler): Route
    {
        return $this->addRoute('PUT', $route, $handler);
    }

    /**
     * @param callable|string $middleware
     * @return MiddlewareAssignable
     */
    public function addMiddleware(callable|string $middleware): MiddlewareAssignable
    {
        if (is_string($middleware) === true) {
            $middleware = $this->container->get($middleware);
        }

        if ($middleware instanceof MiddlewareInterface === false) {
            throw new InvalidArgumentException('Некорректный формат midlleware, объект должен быть имплементировать MiddlewareInterface');
        }

        $this->groupMiddlewares[] = $middleware;

        return $this;
    }
}