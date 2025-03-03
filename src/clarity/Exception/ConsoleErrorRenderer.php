<?php

namespace framework\clarity\Exception;

use framework\clarity\Exception\interfaces\ErrorRenderInterface;

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