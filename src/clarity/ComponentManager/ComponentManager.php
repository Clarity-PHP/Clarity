<?php

namespace framework\clarity\ComponentManager;

use framework\clarity\ComponentManager\interfaces\ComponentInterface;
use framework\clarity\Container\interfaces\ContainerInterface;
use RuntimeException;

class ComponentManager
{
    private array $components = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    /**
     * @param string $key
     * @param string $component
     * @param array $args
     * @return void
     */
    public function register(string $key, string $component, array $args = []): void
    {
        if (is_subclass_of($component, ComponentInterface::class) === false) {
            throw new RuntimeException("Component '{$component}' must implement " . ComponentInterface::class);
        }

        if (isset($this->components[$key]) === true) {
            throw new RuntimeException("Component '{$key}' is already registered");
        }

        $this->components[$key] = $this->container->build($component, $args);
    }

    /**
     * @return void
     */
    public function bootComponents(): void {
        foreach ($this->components as $component) {
            $component->boot();
        }
    }
}