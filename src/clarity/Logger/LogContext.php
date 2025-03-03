<?php

namespace framework\clarity\Logger;

use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Http\ServerRequest;
use framework\clarity\Logger\interfaces\DebugTagStorageInterface;

class LogContext
{
    private ?ServerRequest $request = null;

    /**
     * @param ServerRequest $request
     * @param DebugTagStorageInterface $tagStorage
     * @param mixed|null $data
     * @param int|null $level
     * @param string|null $category
     * @param array $result
     */
    public function __construct(
        public ContainerInterface $container,
        public DebugTagStorageInterface $tagStorage,
        public array $data = [],
        public ?int $level = null,
        public ?string $category = null,
        public array $result = [],
    ) {
        if ($this->container->has(ServerRequest::class) === true) {
            $this->request = $this->container->get(ServerRequest::class);
        }
    }
}