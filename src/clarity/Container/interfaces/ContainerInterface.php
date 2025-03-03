<?php
declare(strict_types=1);

namespace framework\clarity\Container\interfaces;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * @param string $dependencyName
     * @param array $args
     * @return object
     */
    public function build(string $dependencyName, array $args = []): object;

    /**
     * @param object|string $handler
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function call(object|string $handler, string $method, array $args = []): mixed;
}