<?php

namespace framework\clarity\Container;

use framework\clarity\Container\interfaces\ParameterStorageInterface;

class ParameterStorage implements ParameterStorageInterface
{
    private array $parameters = [];

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->parameters[$key] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): void
    {
        unset($this->parameters[$key]);
    }
}