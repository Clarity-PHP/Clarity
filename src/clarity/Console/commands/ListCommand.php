<?php

declare(strict_types=1);

namespace framework\clarity\Console\commands;

use framework\clarity\Console\CommandDefinition;
use framework\clarity\Console\contracts\ConsoleCommandInterface;
use framework\clarity\Console\contracts\ConsoleInputInterface;
use framework\clarity\Console\contracts\ConsoleKernelInterface;
use framework\clarity\Console\contracts\ConsoleOutputInterface;
use ReflectionClass;

class ListCommand implements ConsoleCommandInterface
{
    public static string $signature = 'list';

    public static string $description = 'Тестовая команда';

    private bool $hidden = true;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleKernelInterface $kernel,
        private readonly ConsoleOutputInterface $output,
    ) {
        $this->input->bindDefinitions($this);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->output->info($this->kernel->getAppName());
        $this->output->info(' ' . $this->kernel->getVersion());
        $this->output->writeLn(2);
        $this->output->warning("Фреймворк создан разработчиками Лёвиным А.Д. и Дымовым А.И.\nЯвляется платформой для изучения базового поведения приложения созданного на PHP.\nФреймворк не является production-ready реализацией и не предназначен для коммерческого использования.");
        $this->output->writeLn(2);

        $this->output->success('Доступные опции:');
        $this->output->writeLn();

        foreach ($this->input->getDefaultOptions() as $optionName => $description) {
            $this->output->success('  ' . $optionName);
            $this->output->stdout(' - ' . $description['description']);
            $this->output->writeLn();
        }
        $this->output->writeLn();

        $this->output->success('Вызов:');
        $this->output->writeLn();
        $this->output->stdout('  команда [аргументы] [опции]');
        $this->output->writeLn(2);

        $this->output->stdout('Доступные команды:');
        $this->output->writeLn();

        foreach ($this->kernel->getCommands() as $command) {
            $reflectedClass = new ReflectionClass($command);
            $commandDefinition = new CommandDefinition(
                $reflectedClass->getProperty('signature')->getValue(),
                $reflectedClass->getProperty('description')->getValue()
            );

            $this->output->success('  ' . $commandDefinition->getCommandName());
            $this->output->stdout(' - ' . $commandDefinition->getCommandDescription());
            $this->output->writeLn();
        }
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }


    /**
     * @return string
     */
    public static function getSignature(): string
    {
        return self::$signature;
    }

    /**
     * @return string
     */
    public static function getDescription(): string
    {
        return self::$description;
    }
}