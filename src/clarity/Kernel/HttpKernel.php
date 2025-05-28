<?php

namespace framework\clarity\Kernel;

use framework\clarity\ComponentManager\ComponentManager;
use framework\clarity\Container\DIContainer;
use framework\clarity\Container\interfaces\ParameterStorageInterface;
use framework\clarity\EventDispatcher\interfaces\EventDispatcherInterface;
use framework\clarity\EventDispatcher\Message;
use framework\clarity\Exception\interfaces\ErrorHandlerInterface;
use framework\clarity\Http\interfaces\ResponseInterface;
use framework\clarity\Http\interfaces\ServerRequestInterface;
use framework\clarity\Http\router\exceptions\HttpException;
use framework\clarity\Http\router\interfaces\HTTPRouterInterface;
use framework\clarity\Kernel\interfaces\HttpKernelInterface;
use framework\clarity\Logger\DebugTagGenerator;
use framework\clarity\Logger\interfaces\LoggerInterface;
use framework\clarity\Logger\observers\TagUpdatePreventListener;
use JetBrains\PhpStorm\NoReturn;
use Throwable;

readonly class HttpKernel implements HttpKernelInterface
{
    /**
     * @param ResponseInterface $response
     * @param HTTPRouterInterface $router
     * @param ErrorHandlerInterface $errorHandler
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface $logger
     */
    public function __construct(
        private ResponseInterface $response,
        private HTTPRouterInterface $router,
        private ErrorHandlerInterface $errorHandler,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
        $this->boot();
    }

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->eventDispatcher->attach(KernelEvents::KERNEL_RESPONSE, TagUpdatePreventListener::class);

        $this->eventDispatcher->attach(KernelEvents::KERNEL_REQUEST, TagUpdatePreventListener::class);
    }

    /**
     * @inheritDoc
     */
    #[NoReturn]
    public function shutdown(): void
    {
        exit(0);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->eventDispatcher->trigger(KernelEvents::KERNEL_REQUEST, new Message($this));

        $response = clone $this->response;

        if ($request->getMethod() === 'OPTIONS') {
            return $response
                ->withStatus(204)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-KEY')
                ->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        try {

            $result = $this->router->dispatch($request);

            $contentType = 'text/html';

            $response = $response->withHeader('Content-Type', $contentType);
            $response = $response->withBody($result->getBody());
            $response = $response->withStatus($result->getStatusCode(), $result->getReasonPhrase());

        } catch (HttpException $e) {
            $this->eventDispatcher->trigger(KernelEvents::KERNEL_EXCEPTION, new Message($e));

            $response = $response->withStatus($e->getStatusCode(), $e->getMessage() ?: 'HTTP Error');

            $body = $response->getBody();
            $body->write($this->errorHandler->handle($e));

            $response = $response->withBody($body);

        } catch (Throwable $e) {
            $this->eventDispatcher->trigger(KernelEvents::KERNEL_EXCEPTION, new Message($e));

            $response = $response->withStatus(500, 'Internal Server Error');

            $body = $response->getBody();
            $body->write($this->errorHandler->handle($e));

            $response = $response->withBody($body);
        } finally {

            if ($response->hasHeader('Content-Type') === false) {
                $acceptHeader = $request->getHeaderLine('Accept') ?: 'text/html';
                $response = $response->withHeader('Content-Type', $acceptHeader);
            }


            $bodySize = strlen((string)$response->getBody());
            if ($bodySize > 0) {
                $response = $response->withHeader('Content-Length', (string)$bodySize);
            }


            $response = $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-KEY')
                ->withHeader('Access-Control-Allow-Credentials', 'true');

            $this->eventDispatcher->trigger(KernelEvents::KERNEL_RESPONSE, new Message($this));
        }

        return $response;
    }
}