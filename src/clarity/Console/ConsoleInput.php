<?php

declare(strict_types=1);

namespace framework\clarity\Console;

use framework\clarity\Console\contracts\ConsoleCommandInterface;
use framework\clarity\Console\contracts\ConsoleInputInterface;
use framework\clarity\Console\contracts\ConsoleInputPluginInterface;
use framework\clarity\Console\enums\ConsoleEventsEnum;
use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\EventDispatcher\interfaces\EventDispatcherInterface;
use framework\clarity\EventDispatcher\Message;
use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;

/**
 * Обработка ввода в терминал консоли
 */
final class ConsoleInput implements ConsoleInputInterface
{
    /**
     * @var array аргументы введенные в консоль
     */
    private array $tokens = [];

    /**
     * @var array аргументы, переданные как аргументы вызова в консоль
     */
    private array $arguments = [];

    /**
     * @var array опции, доступные для каждой команды по умолчанию
     */
    private array $defaultOptions = [];

    /**
     * @var array опции переданные как аргументы вызова в консоль
     */
    private array $options = [];

    /**
     * @var CommandDefinition объект описания консольного вызова
     */
    private CommandDefinition $definition;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        $argv ??= $_SERVER['argv'] ?? [];

        array_shift($argv);

        $this->tokens = $argv;
    }

    /**
     * Возврат объекта описания вызванной команды
     *
     * @return CommandDefinition
     */
    public function getDefinition(): CommandDefinition
    {
        return $this->definition;
    }

    /**
     * Регистрация плагинов
     *
     * @param  array $plugins неймспейсы плагинов
     * @return void
     */
    public function addPlugins(array $plugins): void
    {
        foreach ($plugins as $plugin) {
            $reflect = new ReflectionClass($plugin);

            if ($reflect->implementsInterface(ConsoleInputPluginInterface::class) === false) {
                throw new RuntimeException('Класс команды не соответствует интерфейсу' . ConsoleInputPluginInterface::class);
            }

            $this->container->build($plugin);
        }
    }

    /**
     * Получение названия вызываемой команды
     *
     * @return string|null
     */
    public function getCommandName(): string|null
    {
        return $this->tokens[0] ?? null;
    }

    /**
     * Преобразование введенных аргументов в консоль в аргументы и опции вызова команды
     *
     * @return void
     */
    private function parse(): void
    {
        $previousOption = null;

        foreach ($this->tokens as $key => $token) {
            if ($key === 0) {
                continue;
            }

            if (str_starts_with($token, '--')) {
                $previousOption = $this->parseOption($token);

                if (isset($this->options[$previousOption]['value']) === false) {
                    $this->options[$previousOption]['value'] = [];
                }

                continue;
            }

            if ($previousOption !== null) {
                $this->options[$previousOption]['value'][] = $token;

                continue;
            }

            $this->parseArgument($token);
        }
    }


    /**
     * Валиация аргументов, переданных для вызова команды
     *
     * @return void
     */
    private function validate(): void
    {
        foreach ($this->definition->getArguments() as $arg) {
            if (isset($this->arguments[$arg]) === false && $this->definition->isRequired($arg) === true) {
                throw new InvalidArgumentException('Не указан обязательный параметр ' . $arg);
            }
        }
    }

    /**
     * Установка значений по умолчанию для аргументов вызова команды,
     * имеющих значения по умолчанию
     *
     * @return void
     */
    private function setDefaults(): void
    {
        foreach ($this->definition->getArguments() as $arg) {
            if (isset($this->arguments[$arg]) === false && $this->definition->isRequired($arg) === false) {
                $this->arguments[$arg] = $this->definition->getDefaultValue($arg);
            }
        }
    }

    /**
     * Регистрация объекта описания консольного вызова
     *
     * @param ConsoleCommandInterface $command инстанс вызываемой команды
     * @return void
     */
    public function bindDefinitions(ConsoleCommandInterface $command): void
    {
        $this->arguments = [];
        $this->options = array_map(function ($defaultValue) {
            return [
                'passed' => false,
                'value' => null,
            ];
        }, array_fill_keys(array_keys($this->defaultOptions), null));

        $this->dispatcher->trigger(ConsoleEventsEnum::CONSOLE_INPUT_BEFORE_PARSE->name, new Message($this));

        $reflectedClass = new ReflectionClass($command);

        $this->definition = new CommandDefinition(
            $reflectedClass->getProperty('signature')->getValue(),
            $reflectedClass->getProperty('description')->getValue()
        );

        $this->parse();

        $this->dispatcher->trigger(ConsoleEventsEnum::CONSOLE_INPUT_AFTER_PARSE->name, new Message($this));

        $this->validate();
        $this->setDefaults();
    }

    /**
     * Регистрация вызванной опции
     *
     * @param string $option имя опции
     * @return void
     */
    private function parseOption(string $option): ?string
    {
        $option = substr($option, 2);

        $options = array_merge(array_keys($this->options), $this->definition->getOptions());

        if (in_array($option, $options) === false) {
            throw new InvalidArgumentException(sprintf('Опция "--%s" не существует', $option));
        }

        $this->options[$option] = [
            'passed' => true,
            'value' => null,
        ];

        return $option;
    }

    /**
     * Установка значения для аргумента
     *
     * @param string $name имя аргумента
     * @param null|string $value значение аргумента
     * @return void
     */
    public function setArgumentValue(string $name, null|string $value): void
    {
        $this->arguments[$name] = is_numeric($value) ? (int) $value : $value;
    }

    /**
     * Регистрация вызванного аргумента
     *
     * @param string $arg введенный аргумент
     * @return void
     */
    private function parseArgument(string $arg): void
    {
        foreach ($this->definition->getArguments() as $name) {


            if (isset($this->arguments[$name]) === true) {
                continue;
            }

            $this->setArgumentValue($name, $arg);
            return;
        }

        throw new InvalidArgumentException('Слишком много аргументов. Ожидается аргументов: ' . count($this->arguments));
    }

    /**
     * Проверка наличия зарегистрированного аргумента
     *
     * @param string $name имя аргумента
     * @return bool
     */
    public function hasArgument(string $name): bool
    {
        if (isset($this->arguments[$name]) === true){
            return true;
        }

        return false;
    }

    /**
     * Получить значение аргумента
     *
     * @param string $name имя аргумента
     * @return int|string
     */
    public function getArgument(string $name): int|string
    {
        if (array_key_exists($name, $this->arguments) === false) {
            throw new InvalidArgumentException(sprintf('Аргумент "%s" не существует', $name));
        }

        return $this->arguments[$name];
    }

    /**
     * Регистрация опции по-умолчанию
     *
     * @param string $name имя опции
     * @param string $description описание действия опции
     * @return void
     */
    public function addDefaultOption(string $name, string $description): void
    {
        $this->defaultOptions[$name] = [
            'description' => $description,
        ];
    }

    /**
     * Проверка наличия установленной опции вызова
     *
     * @param string $name имя опции
     * @return bool
     */
    public function hasOption(string $name): bool
    {
        if (array_key_exists($name, $this->options) && $this->options[$name]['passed'] === true){
            return true;
        }

        return false;
    }

    /**
     * Получить значение опции
     *
     * @param string $name имя аргумента
     * @return array|null
     */
    public function getOption(string $name): array|null
    {
        if (array_key_exists($name, $this->options) === false) {
            throw new InvalidArgumentException(sprintf('Опция "%s" не существует', $name));
        }

        $value = $this->options[$name]['value'];

        return empty($value) === true ? null : $value;
    }


    /**
     * Получить опции по умолчанию
     *
     * @return array
     */
    public function getDefaultOptions(): array
    {
        return $this->defaultOptions;
    }

    /**
     * Принудительно активировать опцию вызова команды
     *
     * @param string $name имя опции
     * @return void
     */
    public function enableOption(string $name): void
    {
        $this->options[$name]['passed'] = true;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
