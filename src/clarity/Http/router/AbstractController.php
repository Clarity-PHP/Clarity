<?php

namespace framework\clarity\Http\router;

use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Http\interfaces\ResponseInterface;
use framework\clarity\Http\JsonResponse;
use framework\clarity\Http\router\interfaces\ControllerInterface;
use framework\clarity\Http\Stream;
use framework\clarity\Kernel\messages\ExceptionMessage;
use framework\clarity\view\interfaces\ViewRendererInterface;
use RuntimeException;

abstract class AbstractController implements ControllerInterface
{
    protected ?ContainerInterface $container = null;
    protected string $name;
    public string $layout;

    public function __construct(
        private readonly ViewRendererInterface $renderComponent
    ) {
        $this->name = trim(strtolower(str_replace('Controller', '', $this::class)));
    }

    /**
     * Установка контейнера
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Получение зависимости из контейнера
     *
     * @param string $id
     * @return object
     */
    protected function get(string $id): object
    {
        if ($this->container === null) {
            throw new RuntimeException('Container is not set.');
        }

        return $this->container->get($id);
    }

    /**
     * Подготовка пути для представления
     *
     * @param string $view
     * @return string
     */
    protected function preparePath(string $view): string
    {
        return $this->name . '\\' . $view;
    }

    /**
     * Подготовка ответа
     *
     * @param string $content
     * @return ResponseInterface
     */
    protected function prepareResponse(string $content): ResponseInterface
    {
        $stream = $this->createStream($content);

        /** @var ResponseInterface $response */
        $response = $this->get(ResponseInterface::class);

        $response->setBody($stream);

        $response->setHeader('Content-Length', (string)$stream->getSize());

        $response->setHeader('Content-Type', 'text/html; charset=UTF-8');

        return $response;
    }

    /**
     * Создание потока из содержимого.
     *
     * @param string $content
     * @return Stream
     */
    protected function createStream(string $content): Stream
    {
        $stream = new Stream(fopen('php://temp', 'r+'));

        $stream->write($content);

        $stream->rewind();

        return $stream;
    }


    /**
     * Рендер представления
     *
     * @param string $view
     * @param array $params
     * @return ResponseInterface
     */
    public function render(string $view, array $params = []): ResponseInterface
    {
        $viewPath = $this->preparePath($view);

        if (isset($this->layout) === true) {
            $this->renderComponent->setLayout($this->layout);
        }

        $content = $this->renderComponent->render($viewPath, $params);

        return $this->prepareResponse($content);
    }

    /**
     * Рендер JSON-ответа
     *
     * @param array $data
     * @param int $status
     * @return ResponseInterface
     */
    public function renderJson(array $data, int $status = 200): ResponseInterface
    {
        return new JsonResponse($data, $status);
    }

    /**
     * Установка layout
     *
     * @param string $layout
     * @return void
     */
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }
}