<?php

namespace framework\clarity\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Middleware
{
    /**
     * @param string $name
     * @param array $parameters
     * @param bool $enabled
     */
    public function __construct(
        public string $name,
        public array $parameters = [],
        public bool $enabled = true,
    ) {}
}