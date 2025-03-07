<?php

declare(strict_types=1);

namespace framework\clarity\Http\router;

use FilesystemIterator;
use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Http\attributes\Csrf;
use framework\clarity\Http\interfaces\ResponseInterface;
use framework\clarity\Http\interfaces\ServerRequestInterface;
use framework\clarity\Http\router\exceptions\HttpNotFoundException;
use framework\clarity\Http\router\interfaces\HTTPRouterInterface;
use framework\clarity\Http\router\interfaces\MiddlewareAssignable;
use framework\clarity\Http\router\interfaces\MiddlewareInterface;
use framework\clarity\Http\router\middlewares\CsrfMiddleware;
use InvalidArgumentException;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;

class HTTPRouter implements HTTPRouterInterface, MiddlewareAssignable
{
    private array $config = [];

    public array $groups = [];
    public array $routes = [];
    public array $prefix = [];
    public string $methodPath = '';
    public array $globalMiddlewares = [];

    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    /**
     * Добавление маршрута для метода GET
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function get(string $route, string|callable $handler): Route
    {
        return $this->add('GET', $route, $handler);
    }

    /**
     * Добавление маршрута для метода POST
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function post(string $route, string|callable $handler): Route
    {
        return $this->add('POST', $route, $handler);
    }

    /**
     * Добавление маршрута для метода PUT
     *
     * @param string $route путь
     * @param string|callable $handler обработчик, коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function put(string $route, string|callable $handler): Route
    {
        return $this->add('PUT', $route, $handler);
    }

    /**
     * Добавление маршрута для метода PATCH
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function patch(string $route, string|callable $handler): Route
    {
        return $this->add('PATCH', $route, $handler);
    }

    /**
     * Добавление маршрута для метода DELETE
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function delete(string $route, string|callable $handler): Route
    {
        return $this->add('DELETE', $route, $handler);
    }

    /**
     * @param string $method
     * @param string $route
     * @param string|callable $handler
     * @return Route
     */
    public function add(string $method, string $route, string|callable $handler): Route
    {
        $method = strtoupper($method);

        $path = '/';

        foreach ($this->prefix as $prefix) {
            $path .= trim($prefix, '/') . '/';
        }

        $params = $this->prepareParams($route);

        $this->methodPath = trim(trim(strtok($route, '?')), '/');

        $path .= rtrim($this->methodPath, '/');

        if (
            $path !== '/'
            && ($this->methodPath === '' || $this->methodPath = '/')
        ) {
            $path = rtrim($path, '/');
        }

        [$handler, $action] = $this->resolveHandler($handler);

        $this->routes[$method][$path] = new Route(
            $this->container,
            $method,
            $path,
            $handler,
            $action,
            $params,
        );

        return $this->routes[$method][$path];
    }

    /**
     * @param callable|string $middleware
     * @return MiddlewareAssignable
     */
    public function addMiddleware(callable|string $middleware): MiddlewareAssignable
    {
        if (is_callable($middleware) === true){
            $this->globalMiddlewares[] = $middleware;
            return $this;
        }

        if (is_string($middleware) === true) {
            $middleware = $this->container->get($middleware);
        }

        if ($middleware instanceof MiddlewareInterface === false) {
            throw new InvalidArgumentException('Некорректный формат midlleware, объект должен быть имплементировать MiddlewareInterface');
        }

        $this->globalMiddlewares[] = $middleware;

        return $this;
    }

    /**
     * @param string $url
     * @param string $method
     * @return Route|null
     */

    public function findRoute(string $url, string $method): ?Route
    {
        if (
            isset($this->routes[$method]) === true
            && isset($this->routes[$method][$url]) === true
        ) {
            return $this->routes[$method][$url];
        }

        return null;
    }

    /**
     * @param callable|string $handler
     * @return array
     */
    private function resolveHandler(callable|string $handler): array
    {
        if (is_callable($handler) === true) {
            return [$handler, null];
        }


        if (is_string($handler) === true && str_contains($handler, '::') === true) {
            return explode('::', $handler);
        }

        throw new InvalidArgumentException('Неверный формат обработчика');
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws HttpNotFoundException
     * @throws ReflectionException
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        if (isset($this->routes[$method][$path]) === false) {
            throw new HttpNotFoundException('Страница не найдена');
        }

        $route = $this->routes[$method][$path];

        $params = $this->mapParams($request->getQueryParams(), $route->params);

        $controller = $this->container->build($route->handler);

        $controller->setContainer($this->container);

        $action = $route->action;

        if ($this->getCsrfAttribute($controller, $action) === true) {
            $route->addMiddleware(CsrfMiddleware::class);
        }

        $middlewares = array_merge(
            $this->globalMiddlewares ?? [],
            $route->routeMiddlewares ?? []
        );

        foreach (array_keys($this->groups) as $group) {
            $middlewares = array_merge(
                $this->checkForGroupMiddlewares($group, $path),
                $middlewares
            );
        }

        $response = $this->container->get(ResponseInterface::class);

        $this->handleMiddlewares($middlewares, $request, $response);

        return $this->container->call($controller, $action, $params);
    }

    /**
     * @param string $route
     * @return array
     */
    private function prepareParams(string $route): array
    {
        preg_match_all('/{(\??\w+)(?:=(\w+))?}/', $route, $matches, PREG_SET_ORDER);

        $params = [];

        foreach ($matches as $match) {
            $params[] = [
                'name' => $match[0][1] !== '?' ? $match[1] : substr($match[1], 1),
                'required' => $match[0][1] !== '?',
                'default' => $match[2] ?? null,
            ];
        }

        return $params;
    }

    /**
     * @param array $queryParams
     * @param array $params
     * @return array
     */
    private function mapParams(array $queryParams, array $params): array
    {
        $result = [];

        foreach ($params as $param) {
            if (isset($queryParams[$param['name']]) === true) {
                $result[] = $queryParams[$param['name']];

                continue;
            }

            if (isset($param['default']) === true) {
                $result[] = $param['default'];

                continue;
            }

            if ($param['required'] === false) {
                $result[] = null;

                continue;
            }

            throw new InvalidArgumentException("Обязательный параметр {$param['name']} не найден в запросе");
        }

        return $result;
    }

    /**
     * @param string $name
     * @param callable $handler
     * @return RouteGroup
     */
    public function group(string $name, callable $handler): RouteGroup
    {
        $previousPrefix = $this->prefix;

        $this->prefix[] = rtrim($name, '/');

        $group = new RouteGroup($this, $this->container, $this->prefix);

        $this->groups[implode('/', $this->prefix)] = $group;

        $handler($this);

        $this->prefix = $previousPrefix;

        return $group;
    }

    /**
     * @param array $config
     * @return void
     */
    public function configure(array $config): void
    {
        $this->config = $config;

        $projectDir = rtrim($config['kernel.project_dir'], DIRECTORY_SEPARATOR);

        $routesDirectory = $projectDir . DIRECTORY_SEPARATOR . (rtrim($config['routes.directory'], DIRECTORY_SEPARATOR) ?? 'config/routes');

        if (is_dir($routesDirectory) === false) {
            throw new LogicException("Директория с маршруты не найдена: {$routesDirectory}");
        }

        $routeFiles = $this->getRouterConfigFiles($routesDirectory);

        foreach ($routeFiles as $file) {
            if (is_callable($fileContent = require $file) === true) {
                $fileContent($this);
            }
        }
    }

    /**
     * @param string $directory
     * @return array
     */
    private function getRouterConfigFiles(string $directory): array
    {
        $files = [];

        if (is_dir($directory) === false) {
            return $files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {

            if (
                $file->isFile() === true
                && $file->getExtension() === 'php'
            ) {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }

    /**
     * @param object|string $handler
     * @param string|null $action
     * @return bool
     * @throws ReflectionException
     */
    protected function getCsrfAttribute(object|string $handler, ?string $action = null): bool
    {
        if (is_string($handler) && str_contains($handler, '::') === true) {
            [$className, $methodName] = explode('::', $handler);

            $reflectionMethod = new ReflectionMethod($className, $methodName);

            return $this->hasCsrfAttribute($reflectionMethod->getAttributes(Csrf::class)) === true;
        }

        $reflection = new ReflectionObject($handler);

        if ($action !== null && $reflection->hasMethod($action) === true) {

            $reflectionMethod = $reflection->getMethod($action);

            return $this->hasCsrfAttribute($reflectionMethod->getAttributes(Csrf::class)) === true;
        }

        return $this->hasCsrfAttribute($reflection->getAttributes(Csrf::class)) === true;
    }

    /**
     * @param array $attributes
     * @return bool
     */
    private function hasCsrfAttribute(array $attributes): bool
    {
        return array_any($attributes, fn($attribute) => $attribute->getName() === Csrf::class) === true;
    }

    /**
     * @param array $middlewares
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    private function handleMiddlewares(
        array $middlewares, ServerRequestInterface
        $request, ResponseInterface $response,
    ): void {

        $handler = function() use ($request, $response) {
            return $response;
        };

        foreach (array_reverse($middlewares) as $middleware) {
            $next = $handler;
            $handler = function() use ($middleware, $request, $response, $next) {
                $middleware->process($request, $response, $next);
            };
        }

        $handler();
    }

    /**
     * @param string $group
     * @param string $path
     * @return array
     */
    private function checkForGroupMiddlewares(string $group, string $path): array
    {
        $groupMiddlewares = [];

        if (isset($this->groups[$group]) === false) {
            return $groupMiddlewares;
        }

        if (str_contains($path, $group) === true) {
            foreach ($this->groups[$group]->groupMiddlewares as $middleware) {
                $groupMiddlewares[] = $middleware;
            }
        }

        return $groupMiddlewares;
    }
}
