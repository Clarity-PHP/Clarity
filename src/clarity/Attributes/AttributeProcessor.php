<?php

namespace framework\clarity\Attributes;

use framework\clarity\Attributes\interfaces\AttributeProcessorInterface;

class AttributeProcessor implements AttributeProcessorInterface
{
    /**
     * @param array $attributes
     */
    public function __construct(
        private array $attributes = [],
    ) {}
}