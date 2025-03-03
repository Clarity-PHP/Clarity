<?php

declare(strict_types=1);

namespace framework\clarity\Exception;

use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Container\interfaces\ParameterStorageInterface;
use framework\clarity\Exception\interfaces\ErrorHandlerInterface;
use framework\clarity\Exception\interfaces\ErrorHandlingStrategyInterface;
use framework\clarity\Exception\strategies\ConsoleErrorHandlingStrategy;
use framework\clarity\Exception\strategies\HttpErrorHandlingStrategy;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
    private ErrorHandlingStrategyInterface $strategy;

    private array $config = [];

    /**
     * @param ContainerInterface $container
     * @param ParameterStorageInterface $paramStorage
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ParameterStorageInterface $paramStorage,
    ) {
        set_error_handler([$this, 'handle']);

        $this->config = $this->paramStorage->get('errorHandler') ?? [];

        $this->strategy = $this->detectStrategy();
    }

    /**
     * @param Throwable $e
     * @return string
     */
    public function handle(Throwable $e): string
    {
        return $this->strategy->handle($e);
    }

    /**
     * @return ErrorHandlingStrategyInterface
     */
    private function detectStrategy(): ErrorHandlingStrategyInterface
    {

        if (PHP_SAPI === 'cli') {
            return new ConsoleErrorHandlingStrategy();
        }

        $strategy = $this->container->build(HttpErrorHandlingStrategy::class, [
            'templates' => $this->config['templates'] ?? [],
            'defaultTemplate' =>  $this->config['defaultTemplate'] ?? __DIR__ . '/../resources/view/errors/errors_template',
            'defaultLayout' => $this->config['defaultLayout'] ?? null,
            'renderers' => $this->config['renderers'] ?? []
        ]);

        if ($strategy instanceof ErrorHandlingStrategyInterface === false) {
            throw new \InvalidArgumentException(sprintf(
                'Передан недействительный тип стратегии! Ожидался тип %s, получен %s.',
                ErrorHandlingStrategyInterface::class,
                is_object($strategy) ? get_class($strategy) : gettype($strategy)
            ));
        }

        return $strategy;
    }
}