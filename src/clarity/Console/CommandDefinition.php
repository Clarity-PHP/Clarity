<?php

declare(strict_types=1);

namespace framework\clarity\Console;

use framework\clarity\Console\dto\ArgumentDTO;
use framework\clarity\Console\dto\OptionDto;
use InvalidArgumentException;

/**
 * Хранение описания вызванной команды
 */
final class CommandDefinition
{
    /**
     * Информация о вызванной команде: имя, описание
     * @var array
     */
    private array $commandInfo = [
        'name' => null,
        'description' => null,
    ];

    /**
     * Аргументы команды
     * @var array
     */
    private array $arguments = [];

    /**
     * Опции команды
     * @var array
     */
    private array $options = [];

    public function __construct(string $signature, string $description)
    {
        $this->initDefinitions($signature);
        $this->commandInfo['description'] = $description;
    }

    /**
     * Возврат имен аргументов команды
     *
     * @return array
     */
    public function getArguments(): array
    {
        return array_keys($this->arguments);
    }

    /**
     * Возврат имен опций команды
     *
     * @return array
     */
    public function getOptions(): array
    {
        return array_keys($this->options);
    }

    /**
     * Возврат имени вызванной команды
     *
     * @return string
     */
    public function getCommandName(): string
    {
        return $this->commandInfo['name'];
    }

    /**
     * Возврат описания вызванной команды
     *
     * @return string
     */
    public function getCommandDescription(): string
    {
        return $this->commandInfo['description'];
    }

    /**
     * Возврат параметров, определенных для аргумента:
     * описание, обязательный да/нет, значение по умолчанию
     *
     * @param  string $name имя аругмента
     * @return ArgumentDTO
     */
    public function getArgumentDefinition(string $name): ArgumentDTO
    {
        if (array_key_exists($name, $this->arguments) === false) {
            throw new InvalidArgumentException("Аргумент с именем '$name' не найден");
        }

        return $this->arguments[$name];
    }

    /**
     * Возврат параметров, определенных для опции:
     * описание
     *
     * @param  string $name имя опции
     * @return ArgumentDTO
     */
    public function getOptionDefinition(string $name): ArgumentDTO
    {
        if (array_key_exists($name, $this->options) === false) {
            throw new InvalidArgumentException("Опция с именем '$name' не найдена");
        }

        return $this->options[$name];
    }

    /**
     * Определение аргумента, установленного обязательным
     *
     * @param  string $name имя аргумента
     * @return bool
     */
    public function isRequired(string $name): bool
    {
        if (array_key_exists($name, $this->arguments) === false) {
            throw new InvalidArgumentException("Аргумент с именем '$name' не найден");
        }

        return $this->arguments[$name]->required;
    }

    /**
     * Возврат значения по умолчанию,
     * установленного для аргумента
     *
     * @param  string $name имя аргумента
     * @return mixed
     */
    public function getDefaultValue(string $name): mixed
    {
        if (array_key_exists($name, $this->arguments) === false) {
            throw new InvalidArgumentException("Аргумент с именем '$name' не найден");
        }

        return $this->arguments[$name]->defaultValue;
    }

    /**
     * Формирование параметров, определенных для опций и аргументов
     *
     * @param  string $signature строка описания команды
     * @return void
     */
    private function initDefinitions(string $signature): void
    {
        if ((bool) preg_match('/^([\w\S]+)/', $signature, $matches) === false) {
            throw new InvalidArgumentException('Неверная сигнатура: имя команды не найдено');
        }

        $this->commandInfo['name'] = $matches[1];

        if (preg_match_all('/{\s*(.*?)\s*}/', $signature, $matches) === 0) {
            return;
        }

        foreach ($matches[1] as $param) {
            if ((bool)preg_match('/--(.*)/', $param, $matches) === true) {
                $this->initOption($param);

                continue;
            }

            $this->initArgument($param);
        }
    }

    /**
     * Определение параметров, определенных для опций
     *
     * @param  string $option строка зарегистрированной опции
     * @return void
     */
    private function initOption(string $option): void
    {
        if ((bool)preg_match('/^--([a-zA-Z0-9\-]+)/', $option, $matches) === false) {
            throw new InvalidArgumentException("Неверный формат опции: $option");
        }

        if (isset($this->options[$matches[1]]) === true) {
            throw new InvalidArgumentException("Дублирование опции: $matches[1]");
        }

        $this->options[$matches[1]] = new OptionDTO();

        if ((bool)preg_match('/:(.*)$/', $option, $descMatches) === true) {
            $this->options[$matches[1]]->description = trim($descMatches[1]);
        }
    }

    /**
     * Определение параметров, определенных для аргументов
     *
     * @param  string $arg строка зарегистрированного аргумента
     * @return void
     */
    private function initArgument(string $arg): void
    {
        if (preg_match('/(\w+)/', $arg, $matches) === 0) {
            throw new InvalidArgumentException("Неверный формат аргумента: $arg");
        }

        if (isset($this->arguments[$matches[1]]) === true) {
            throw new InvalidArgumentException("Дублирование аргумента: $matches[1]");
        }

        $this->arguments[$matches[1]] = new ArgumentDTO();

        $this->arguments[$matches[1]]->required = str_contains($arg, '?') === false;

        if ((bool)preg_match('/:(.*?)(?==|$)/', $arg, $descMatches) === true) {
            $this->arguments[$matches[1]]->description = trim($descMatches[1]);
        }

        if ((bool)preg_match('/(?<==)[\wA-z]+/', $arg, $defaultMatches) === true) {
            $this->arguments[$matches[1]]->defaultValue = $defaultMatches[0];
        }
    }
}
