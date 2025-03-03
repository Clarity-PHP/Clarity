<?php
declare(strict_types=1);

namespace framework\clarity\view;

use Exception;
use framework\clarity\Container\interfaces\ParameterStorageInterface;
use framework\clarity\Helpers\Alias;
use framework\clarity\Helpers\PathHelper;
use framework\clarity\view\exceptions\ViewNotFoundException;
use framework\clarity\view\interfaces\AssetManagerInterface;
use framework\clarity\view\interfaces\ViewRendererInterface;
use InvalidArgumentException;
use RuntimeException;

class ViewRenderer implements ViewRendererInterface
{
    private ?string $layout = null;

    private array $params = [];

    private array $headerElements = [];

    /**
     * @param AssetManagerInterface $assetManager
     * @param ParameterStorageInterface $paramStorage
     * @throws Exception
     */
    public function __construct(
        private readonly AssetManagerInterface $assetManager,
        private readonly ParameterStorageInterface $paramStorage,
    ) {
        $assetUrl = $this->paramStorage->get('kernel.asset_url');

        if ($assetUrl === null) {
            throw new InvalidArgumentException('Required parameter "kernel.asset_url" not set.');
        }

        if (str_starts_with($assetUrl, '@') === true) {
            $assetUrl = Alias::get($assetUrl);
        }

        $this->assetManager->assetsPath = $assetUrl;
    }

    /**
     * @return string|null
     */
    public function getLayout(): ?string
    {
        return $this->layout;
    }

    /**
     * @param string|null $layout
     * @return void
     */
    public function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function render(string $view, array $params = []): string
    {
        $viewPath = $this->prepareView($view);

        if ($this->layout !== null) {
            $this->layout = str_contains($this->layout,
                '@') ? Alias::get($this->layout) . '.php' : $this->layout . '.php';
        }

        $this->extractParams($params);

        return $this->bufferRender($viewPath);
    }

    /**
     * @param string $view
     * @return string
     * @throws Exception
     */
    private function prepareView(string $view): string
    {
        if ($view === '') {
            throw new ViewNotFoundException('Передан пустой шаблон.');
        }

        if (PathHelper::isAbsolutePath($view) === true) {
            $filePath = $view . '.php';

            if (file_exists($filePath) === false) {
                throw new InvalidArgumentException('Файл не существует: ' . $filePath);
            }

            $resolvedPath = realpath($filePath);

            if ($resolvedPath === false) {
                throw new RuntimeException('Не удалось определить абсолютный путь: ' . $filePath);
            }

            return $resolvedPath;
        }

        // Для алиасов: получаем путь и проверяем его существование
        if (str_contains($view, '@') === true) {
            $resolvedPath = Alias::get($view) . '.php';

            if (file_exists($resolvedPath) === false) {
                throw new ViewNotFoundException('Шаблон не найден: ' . $resolvedPath);
            }

            return $resolvedPath;
        }

        // Для относительных путей: создаем путь и проверяем его существование
        $controllerPath = str_replace(
            ['controllers/'],
            'views/',
            str_replace(['\\'], DIRECTORY_SEPARATOR, $view)
        );

        $filePath = PathHelper::joinPaths(
            Alias::get('@app') ?? __DIR__ . '/../',
            trim($controllerPath, '/') . '.php'
        );

        if (file_exists($filePath) === false) {
            throw new ViewNotFoundException('Шаблон не найден: ' . $filePath);
        }

        return $filePath;
    }

    /**
     * @param array $params
     * @return void
     */
    private function extractParams(array $params): void
    {
        if (empty($params) === true) {
            return;
        }

        foreach ($params as $key => $value) {
            if (
                is_string($key) === true
                && (bool)preg_match('/^[a-zA-Z_]\w*$/', $key) === true
            ) {
                $this->params[$key] = $value;

                continue;
            }

            throw new InvalidArgumentException("Недопустимый ключ параметра: {$key}");
        }
    }

    /**
     * @param string $viewPath
     * @return string
     * @throws ViewNotFoundException
     */
    private function bufferRender(string $viewPath): string
    {
        ob_start();

        try {
            extract($this->params, EXTR_SKIP);

            $this->renderHeadElements();

            include $viewPath;

            $content = ob_get_clean();

            if ($this->layout !== null) {

                if (file_exists($this->layout) === false) {
                    throw new ViewNotFoundException($this->layout);
                }

                ob_start();

                include $this->layout;

                $layoutContent = ob_get_clean();

                return str_replace('{{content}}', $content, $layoutContent);
            }

            return $content;

        } catch (RuntimeException $e) {
            ob_end_clean();

            throw $e;
        }
    }

    /**
     * @param $element
     * @return void
     */
    public function addHeadElement($element): void
    {
        $this->headerElements[] = $element;
    }

    /**
     * @param string $asset
     * @return string
     */
    public function asset(string $asset): string
    {
        return $this->assetManager->getAssetPath($asset);
    }

    /**
     * @return void
     */
    private function renderHeadElements(): void
    {
        foreach ($this->headerElements as $element) {
            echo $element;
        }
    }

}