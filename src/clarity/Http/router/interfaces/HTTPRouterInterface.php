<?php

declare(strict_types=1);

namespace framework\clarity\Http\router\interfaces;

use framework\clarity\Http\interfaces\ServerRequestInterface;
use framework\clarity\Http\router\exceptions\HttpNotFoundException;
use framework\clarity\Http\router\Route;
use framework\clarity\Http\router\RouteGroup;

interface HTTPRouterInterface
{
    /**
     * @param string $name
     * @param string $controller
     * @param array $config
     * @return Route
     */
    public function addResource(string $name, string $controller, array $config = []): void;

    /**
     * Добавление маршрута для метода GET
     *
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function get(string $route, string|callable $handler): Route;

    /**
     * Добавление маршрута для метода POST
     *
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function post(string $route, string|callable $handler): Route;

    /**
     * Добавление маршрута для метода PUT
     *
     * @param  string $route путь
     * @param  string|callable $handler обработчик, коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function put(string $route, string|callable $handler): Route;

    /**
     * Добавление маршрута для метода PATCH
     *
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function patch(string $route, string|callable $handler): Route;

    /**
     * Добавление маршрута для метода DELETE
     *
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function delete(string $route, string|callable $handler): Route;

    /**
     * Добавление группы машрутов
     *
     * Пример:
     * /api/v1/path
     * $router->group('api', function (HTTPRouterInterface $router) {
     *
     *     $router->group('v1', function (HTTPRouterInterface $router) {
     *
     *         $router->get('/path', SomeHandler::class . '::action');
     *
     *     });
     *
     * });
     *
     * @param  string $name имя группы
     * @param  callable $handler функция-сборщик маршрута группы
     * @return RouteGroup
     */
    public function group(string $name, callable $handler): RouteGroup;

    /**
     * Добавление маршрута для метода запроса
     *
     * @param  string $method метод запроса
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function add(string $method, string $route, string|callable $handler): Route;

    /**
     * Диспетчеризация входящего запроса
     *
     * @param  ServerRequestInterface $request объект запроса
     * @return mixed
     * @throws HttpNotFoundException если маршрут не зарегистрирован в конфигурации машрутов
     */
    public function dispatch(ServerRequestInterface $request): mixed;

    /**
     * @param array $config
     * @return void
     */
    public function configure(array $config): void;
}
