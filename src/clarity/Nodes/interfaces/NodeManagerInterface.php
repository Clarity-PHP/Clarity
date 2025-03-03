<?php

namespace framework\clarity\Nodes\interfaces;

interface NodeManagerInterface
{
    /**
     * @return void
     */
    public function init(): void;

    /**
     * @return array
     */
    public function getNodes(): array;

    /**
     * @param string $name
     * @return array|null
     */
    public function getNode(string $name): ?array;
}