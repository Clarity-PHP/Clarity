<?php

namespace framework\clarity\Exception;

use Throwable;

class ErrorRendererContext
{
    /**
     * @param int $statusCode
     * @param Throwable $throwable
     * @param string|null $template
     * @param string|null $layout
     */
    public function __construct(
        public int $statusCode,
        public Throwable $throwable,
        public ?string $template = null,
        public ?string $layout = null
    ) {}
}