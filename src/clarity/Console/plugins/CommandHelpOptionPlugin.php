<?php

declare(strict_types=1);

namespace framework\clarity\Console\plugins;

use framework\clarity\Console\contracts\ConsoleInputInterface;
use framework\clarity\Console\contracts\ConsoleInputPluginInterface;
use framework\clarity\Console\contracts\ConsoleKernelInterface;
use framework\clarity\Console\contracts\ConsoleOutputInterface;
use framework\clarity\Console\enums\ConsoleEventsEnum;
use framework\clarity\EventDispatcher\interfaces\EventDispatcherInterface;
use framework\clarity\EventDispatcher\interfaces\ObserverInterface;
use framework\clarity\EventDispatcher\Message;

/**
 * Плагин вывода информации о команде
 */
class CommandHelpOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private string $optionName;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ConsoleKernelInterface $kernel,
    )
    {
        $this->optionName = 'help';
        $this->input->addDefaultOption($this->optionName, 'Вывод информации о команде');

        $this->dispatcher->attach(ConsoleEventsEnum::CONSOLE_INPUT_AFTER_PARSE->name, $this::class);
    }

    public function handle(Message $message): void
    {
        if ($this->input->hasOption($this->optionName) === false) {
            return;
        }

        $intent = '    ';

        $commandDefinition = $this->input->getDefinition();

        $this->output->success('Вызов:');

        $this->output->writeLn();

        $this->output->stdout($intent . $this->input->getCommandName());

        foreach ($commandDefinition->getArguments() as $arg) {
            $this->output->stdout(" [$arg]");
        }

        $this->output->stdout(' [опции]');

        $this->output->writeLn();

        $this->output->info('Назначение:');

        $this->output->writeLn();

        $this->output->stdout($intent . $commandDefinition->getCommandDescription());

        $this->output->writeLn();

        if (empty($commandDefinition->getArguments()) === false) {
            foreach ($commandDefinition->getArguments() as $arg) {
                $this->output->info('Аргументы:');

                $this->output->writeLn();

                $this->output->success($intent . $arg);

                $this->output->stdout(' - ' . $commandDefinition->getArgumentDefinition($arg)->description);

                if ($commandDefinition->getArgumentDefinition($arg)->required === true) {
                    $this->output->stdout(', обязательный параметр');
                }

                if ($commandDefinition->getArgumentDefinition($arg)->required === false) {
                    $this->output->stdout( ', необязательный параметр');
                }


                if (isset($commandDefinition->getArgumentDefinition($arg)->defaultValue) === true) {
                    $this->output->stdout(', значение по-умолчанию: ' . $commandDefinition->getArgumentDefinition($arg)->defaultValue);
                }

                $this->output->writeLn();
            }
        }

        if (empty($this->input->getDefaultOptions()) === false) {
            $this->output->info('Опции:');

            $this->output->writeLn();

            foreach ($this->input->getDefaultOptions() as $key => $option) {
                $this->output->success($intent . $key);

                $this->output->stdout(' ' . $option['description']);

                $this->output->writeLn();
            }
        }

        if (empty($commandDefinition->getOptions()) === false) {
            foreach ($commandDefinition->getOptions() as $option) {
                $this->output->success($intent . $option);

                $this->output->stdout(' ' . $commandDefinition->getOptionDefinition($option)->description);

                $this->output->writeLn();
            }

            $this->output->writeLn();
        }

        $this->kernel->terminate(0);
    }
}
