<?php

namespace framework\clarity\Exception;

use framework\clarity\Container\interfaces\ParameterStorageInterface;
use framework\clarity\Exception\interfaces\ErrorRenderInterface;
use Throwable;

readonly class JsonErrorRenderer implements ErrorRenderInterface
{
    /**
     * @param ParameterStorageInterface $storage
     */
    public function __construct(
        private ParameterStorageInterface $storage,
    ) {}

    /**
     * @inheritDoc
     */
    public function handle(ErrorRendererContext $context): string
    {
        $errorResponse = [
            'error' => $context->throwable->getMessage(),
            'statusCode' => $context->statusCode,
        ];

        if ($this->storage->get('app.environment') !== 'production') {
            $errorResponse['stackTrace'] = $context->throwable->getTraceAsString();
        }

        return json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
    }
}