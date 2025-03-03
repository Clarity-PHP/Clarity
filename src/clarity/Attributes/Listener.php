<?php

namespace framework\clarity\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Listener
{
    /**
     * @param string $event
     * @param array $payload
     */
    public function __construct(
        public string $event,
        public array $payload = [],
    ) {}
}