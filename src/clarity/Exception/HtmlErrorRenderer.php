<?php

namespace framework\clarity\Exception;

use framework\clarity\Container\interfaces\ParameterStorageInterface;
use framework\clarity\Exception\interfaces\ErrorRenderInterface;
use framework\clarity\view\interfaces\ViewRendererInterface;

readonly class HtmlErrorRenderer implements ErrorRenderInterface
{
    public function __construct(
        private ViewRendererInterface $renderer,
        private ParameterStorageInterface $paramStorage,
    ) {}

    /**
     * @inheritDoc
     */
    public function handle(ErrorRendererContext $context): string
    {
        $this->renderer->setLayout($context->layout);

        return $this->renderer->render($context->template, [
            'code' => $context->statusCode,
            'message' => $context->throwable->getMessage(),
            'trace' => $context->throwable->getTrace(),
            'file' => $context->throwable->getFile(),
            'line' => $context->throwable->getLine(),
            'environment' => $this->paramStorage->get('app.environment'),
        ]);
    }
}