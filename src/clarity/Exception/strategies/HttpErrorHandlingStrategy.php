<?php

namespace framework\clarity\Exception\strategies;

use framework\clarity\Exception\ErrorRendererContext;
use framework\clarity\Exception\interfaces\ErrorHandlingStrategyInterface;
use framework\clarity\Http\interfaces\ResponseInterface;
use framework\clarity\Http\interfaces\ServerRequestInterface;
use framework\clarity\view\interfaces\ViewRendererInterface;
use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Container\interfaces\ParameterStorageInterface;
use Throwable;

readonly class HttpErrorHandlingStrategy implements ErrorHandlingStrategyInterface
{
    public function __construct(
        private ResponseInterface $response,
        private ServerRequestInterface $request,
        private ViewRendererInterface $viewRenderer,
        private ContainerInterface $container,
        private ParameterStorageInterface $parameterStorage,
        private array $templates = [],
        private ?string $defaultTemplate = null,
        private ?string $defaultLayout = null,
        private array $renderers = []
    ) {}

    /**
     * @param Throwable $e
     * @return string
     */
    public function handle(Throwable $e): string
    {
        $statusCode = $this->response->getStatusCode() ?: 500;

        $acceptHeader = $this->request->getHeader('Accept') ?? 'text/html';

        $acceptHeader = $acceptHeader ? explode(',', $acceptHeader)[0] : 'text/html';

        $template = $this->templates[$statusCode]['template'] ?? $this->defaultTemplate;

        $layout = $this->templates[$statusCode]['layout'] ?? $this->defaultLayout;

        $context = new ErrorRendererContext($statusCode, $e, $template, $layout);

        if (isset($this->renderers[$acceptHeader]) === true) {
            $handler = $this->container->get($this->renderers[$acceptHeader]);

            return $handler->handle($context);
        }

        return $this->viewRenderer->render($context->template, [
            'code' => $context->statusCode,
            'message' => $context->throwable->getMessage(),
            'trace' => $context->throwable->getTrace(),
            'file' => $context->throwable->getFile(),
            'line' => $context->throwable->getLine(),
            'environment' => $this->parameterStorage->get('app.environment'),
        ]);
    }
}