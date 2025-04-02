<?php

declare(strict_types=1);

namespace framework\clarity\Http\router;

use framework\clarity\Http\router\interfaces\HTTPRouterInterface;

class Resource
{
    public function __construct(
        private readonly string $name,
        private readonly string $controller,
        private array  $config = []
    ) {}

    public function build(HTTPRouterInterface $router): void
    {
        $path = $this->name;

        $router->group($path, function (HTTPRouterInterface $router) {
            foreach ($this->setConfiguration($this->name) as $params) {
                $route = $router->add($params['method'], $params['path'], $this->controller . '::' . $params['action']);
                if (empty($params['middleware']) === true) {
                    continue;
                }

                $route->addMiddleware($params['middleware']);
            }
        });
    }

    private function setConfiguration(string $path): array
    {
        return [
            'index' => [
                'method' => 'GET',
                'path' => $path,
                'action' => 'actionList',
                'middleware' => [],
            ],
            'view' => [
                'method' => 'GET',
                'path' => "{$path}/{:id|integer}",
                'action' => 'actionView',
                'middleware' => [],
            ],
            'create' => [
                'method' => 'POST',
                'path' => $path,
                'action' => 'actionCreate',
                'middleware' => [],
            ],
            'put' => [
                'method' => 'PUT',
                'path' => "{$path}/{:id|integer}",
                'action' => 'actionUpdate',
                'middleware' => [],
            ],
            'patch' => [
                'method' => 'PATCH',
                'path' => "{$path}/{:id|integer}",
                'action' => 'actionPatch',
                'middleware' => [],
            ],
            'delete' => [
                'method' => 'DELETE',
                'path' => "{$path}/{:id|integer}",
                'action' => 'actionDelete',
                'middleware' => [],
            ],
        ];
    }
}