<?php

namespace framework\clarity\Exception;

use framework\clarity\Exception\interfaces\ErrorRenderInterface;
use Throwable;

class ConsoleErrorRenderer implements ErrorRenderInterface
{

    /**
     * @inheritDoc
     */
    public function handle(ErrorRendererContext $context): string
    {
        // TODO: Implement handle() method.
    }
}