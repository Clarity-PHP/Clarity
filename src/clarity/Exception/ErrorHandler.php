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
use ErrorException;

class ErrorHandler implements ErrorHandlerInterface
{
    private ErrorHandlingStrategyInterface $strategy;
    private array $config = [];

    public function __construct(
        private readonly ContainerInterface          $container,
        private readonly ParameterStorageInterface   $paramStorage,
    ) {
        set_error_handler([$this, 'handle']);
        set_exception_handler([$this, 'handle']);

        $this->config   = $this->paramStorage->get('errorHandler') ?? [];

        $this->strategy = $this->detectStrategy();
    }

    /**
     * Универсальный обработчик ошибок/исключений.
     *
     * @param mixed       $eOrErrno  либо Throwable, либо код ошибки (int)
     * @param string|null $errstr    текст ошибки (для PHP-ошибок)
     * @param string|null $errfile   файл (для PHP-ошибок)
     * @param int|null    $errline   строка (для PHP-ошибок)
     *
     * @return string  результат стратегии (HTTP-ответ, вывод в консоль и т.п.)
     */
    public function handle(
        mixed $eOrErrno,
        mixed $errstr   = null,
        mixed $errfile  = null,
        mixed $errline  = null
    ): string {
        // Если пришло исключение — сразу отдаем в стратегию
        if ($eOrErrno instanceof Throwable) {
            return $this->strategy->handle($eOrErrno);
        }

        // Иначе это PHP-ошибка: создаем ErrorException и обрабатываем её
        $e = new ErrorException(
            (string)$errstr,
            0,
            (int)$eOrErrno,
            (string)$errfile,
            (int)$errline
        );

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

        $strategy = $this
            ->container
            ->build(HttpErrorHandlingStrategy::class, [
                'templates'       => $this->config['templates']       ?? [],
                'defaultTemplate'=> $this->config['defaultTemplate'] ?? __DIR__ . '/../resources/view/errors/errors_template',
                'defaultLayout'  => $this->config['defaultLayout']   ?? null,
                'renderers'      => $this->config['renderers']       ?? [],
            ]);

        if (!$strategy instanceof ErrorHandlingStrategyInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Недействительный тип стратегии: %s',
                is_object($strategy) ? get_class($strategy) : gettype($strategy)
            ));
        }

        return $strategy;
    }
}
