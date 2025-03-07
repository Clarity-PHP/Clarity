<?php

namespace framework\clarity\Nodes;

use framework\clarity\Container\DIContainer;
use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Helpers\PathHelper;
use framework\clarity\Http\router\HTTPRouter;
use framework\clarity\Http\router\interfaces\HTTPRouterInterface;

abstract class Node
{
    private ?string $lowerName = null;
    private bool $status = true;
    private array $config = [];

    /**
     * @param DIContainer $container
     * @param string $name
     * @param string $path
     */
    public function __construct(
        protected ContainerInterface $container,
        protected string $name,
        protected string $path
    ) {}

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLowerName(): string
    {
        return $this->lowerName ??= strtolower($this->name);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    public function getContainer(): DIContainer
    {
        return $this->container;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     * @return void
     */
    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    /**
     * @return void
     */
    public function init(): void
    {
        if ($this->status === true) {
            $this->loadConfig();
        }
    }

    /**
     * @return void
     */
    private function loadConfig(): void
    {
        $configPath = PathHelper::joinPaths($this->path, 'config');

        $moduleConfig = PathHelper::joinPaths($this->path, 'config', $this->name . '.php');

        if (file_exists($moduleConfig) === true) {
            if (empty($this->config) === true) {
                $this->config = require $moduleConfig;
            }

            $routes = $this->config['router'] ?? [];

            if (
                empty($routes) === true
                || ((bool)preg_match('/^[\w\/]+$/', $routes['routes']) === false)
                || $routes['routes'] === ''
            ) {

                $this->config['router'] = [
                    'routes' => 'routes/',
                ];
            }

            $routesDir = PathHelper::joinPaths($configPath, $this->config['router']['routes']);

            if (is_dir($routesDir) === false) {
                return;
            }

            $routes = PathHelper::getFilesFromDirectory($routesDir);

            $router = $this->container->get(HTTPRouterInterface::class);

            foreach ($routes as $file) {

                require_once $file;

                if (is_callable($fileContent = require $file) === true) {
                    $fileContent($router);
                }
            }
        }
    }
}