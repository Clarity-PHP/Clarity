<?php

declare(strict_types=1);

namespace framework\clarity\Console;

use framework\clarity\Console\commands\ListCommand;
use framework\clarity\Console\contracts\ConsoleCommandInterface;
use framework\clarity\Console\contracts\ConsoleInputInterface;
use framework\clarity\Console\contracts\ConsoleKernelInterface;
use framework\clarity\Console\contracts\ConsoleOutputInterface;
use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Container\interfaces\ParameterStorageInterface;
use framework\clarity\Exception\interfaces\ErrorHandlerInterface;
use framework\clarity\Logger\interfaces\LoggerInterface;
use InvalidArgumentException;
use Throwable;

final class ConsoleKernel implements ConsoleKernelInterface
{
    private string $defaultCommand = 'List';

    private array $commandMap = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly ErrorHandlerInterface $errorHandler,
        private readonly LoggerInterface $logger,
        private readonly ParameterStorageInterface $storage,
    )
    {
        $this->initDefaultCommands();
    }

    /**
     * Возврат имени приложения
     *
     * @return string
     */
    public function getAppName(): string
    {
        return $this->storage->get('app.name');
    }

    /**
     * Возврат версии приложения
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->storage->get('app.version');
    }

    /**
     * Возврат карты команд
     *
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commandMap;
    }


    /**
     * @param array $commandNameSpaces
     * @return void
     */
    public function registerCommandNamespaces(array $commandNameSpaces): void
    {
        foreach ($commandNameSpaces as $namespace) {
            $this->registerCommandNamespace($namespace);
        }
    }


    /**
     * @param string $className
     * @return void
     */
    private function registerCommand(string $className): void
    {
        if (in_array(ConsoleCommandInterface::class, class_implements($className)) === false) {
            throw new InvalidArgumentException("Класс $className не соответствует интерфейсу ConsoleCommandInterface");
        }

        $part = explode('\\', $className);

        $commandName = str_replace('Command', '', end($part));

        $this->commandMap[$commandName] = $className;
    }


    /**
     * @param string $commandNameSpace
     * @return void
     */
    private function registerCommandNamespace(string $commandNameSpace): void
    {
        $paths = glob($commandNameSpace . '/*.php');

        foreach ($paths as $path) {
            if ((bool) preg_match('/namespace\s+([^;]+);/', file_get_contents($path), $matches) === false) {
                continue;
            }

            $namespace = $matches[1] . '\\' .  basename($path, '.php');

            if (class_exists($namespace) === true) {
                $this->registerCommand($namespace);
            }
        }
    }


    /**
     * @return void
     */
    private function initDefaultCommands(): void
    {
        $defaultCommands = [
            ListCommand::class,
        ];

        foreach ($defaultCommands as $className) {
            $this->registerCommand($className);
        }
    }

    /**
     * @return int
     */
    public function handle(): int
    {
        $commandName = $this->input->getCommandName() ?? $this->defaultCommand;

        $commandName = $this->commandMap[$commandName]
            ?? throw new InvalidArgumentException(sprintf("Команда %s не найдена", $commandName));

        try {
            $this->container
                ->build($commandName)
                ->execute($this->input);

        } catch (Throwable $e) {
            $message = $this->errorHandler->handle($e);

            $this->output->stdout($message);

            $this->logger->error($e);

            return 1;
        }

        return 0;
    }

    /**
     * @param int $status
     * @return never
     */
    public function terminate(int $status): never
    {
        exit($status);
    }
}