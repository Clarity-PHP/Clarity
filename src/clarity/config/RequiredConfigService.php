<?php

namespace framework\clarity\config;

use Exception;
use framework\clarity\Container\interfaces\ParameterStorageInterface;
use framework\clarity\Helpers\Alias;
use LogicException;

readonly class RequiredConfigService
{
    /**
     * @throws Exception
     */
    public function __construct(
        private ParameterStorageInterface $paramStorage,
        private array $config = [],
    ) {
        $this->validateRequiredParams();

        $this->initializeAliases();
    }

    /**
     * @return void
     */
    private function validateRequiredParams(): void
    {
        $requiredParams = [
            'app.name',
            'app.environment',
            'kernel.project_dir',
            'kernel.public_dir',
            'nodes',
        ];

        foreach ($requiredParams as $param) {
            if (isset($this->config[$param]) === false) {
                throw new LogicException("Отсутствует обязательный параметр конфигурации: {$param}");
            }
        }

        foreach ($this->config as $key => $value) {
            //if (str_starts_with($key, 'app') === true || str_starts_with($key, 'kernel') === true || str_starts_with($key, 'nodes')) {
                $this->paramStorage->set($key, $value);
            //}
        }
    }

    /**
     * @throws Exception
     */
    private function initializeAliases(): void
    {
        Alias::set('@app', $this->paramStorage->get('kernel.project_dir'));

        Alias::set('@web', $this->paramStorage->get('kernel.public_dir'));

        Alias::set('@views', Alias::get('@app') . DIRECTORY_SEPARATOR . 'views');

        if (isset($this->config['aliases']) === true) {
            foreach ($this->config['aliases'] as $alias => $path) {
                Alias::set($alias, $path);
            }
        }
    }
}