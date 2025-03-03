<?php

namespace framework\clarity\Exception\interfaces;

use framework\clarity\Exception\ErrorRendererContext;

interface ErrorRenderInterface
{
    /**
     * @param ErrorRendererContext $context
     * @return string
     */
    public function handle(ErrorRendererContext $context): string;
}