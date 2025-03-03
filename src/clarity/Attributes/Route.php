<?php

namespace framework\clarity\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route
{
    /**
     * @param string $path
     * @param array $methods
     */
    public function __construct(
        public string $path,
        public array $methods = ['GET']
    ) {}
}