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

        $contentType = $this->request->getHeader('Content-Type');

        $acceptHeader = empty($contentType) === false
            ? (is_array($contentType) ? reset($contentType) : $contentType)
            : ($this->request->getHeader('Accept') ?? '');

        $acceptHeader = match (true) {
            empty($contentType) === false => $acceptHeader,
            $acceptHeader === '*/*' => 'application/json',
            empty($acceptHeader )=== true => 'application/json',
            default => explode(',', $acceptHeader)[0],
        };

        $template = $this->templates[$statusCode]['template'] ?? $this->defaultTemplate;
        $layout = $this->templates[$statusCode]['layout'] ?? $this->defaultLayout;

        $context = new ErrorRendererContext($statusCode, $e, $template, $layout);

        if (isset($this->renderers[$acceptHeader])) {
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