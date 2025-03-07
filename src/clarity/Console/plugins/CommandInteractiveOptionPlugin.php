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
use InvalidArgumentException;

/**
 * Плагин интерактивного ввода параметров
 */
class CommandInteractiveOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private string $optionName;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ConsoleKernelInterface $kernel,
    ) {
        $this->optionName = 'interactive';
        $this->input->addDefaultOption($this->optionName, 'Плагин интерактивного ввода параметров');

        $this->dispatcher->attach(ConsoleEventsEnum::CONSOLE_INPUT_AFTER_PARSE->name, $this::class);
    }

    /**
     * б
     * @param Message $message
     * @return void
     */
    public function handle(Message $message): void
    {
        if ($this->input->hasOption($this->optionName) === false) {
            return;
        }

        foreach ($this->input->getDefinition()->getArguments() as $arg) {

            $this->output->success('Введите аргумент: ');

            $this->output->success($arg);

            $this->output->success(' (' . $this->input->getDefinition()->getArgumentDefinition($arg)->description . ')');

            if ($this->input->getDefinition()->getArgumentDefinition($arg)->defaultValue !== null) {
                $this->output->success(' [' . $this->input->getDefinition()->getArgumentDefinition($arg)->defaultValue . ']');
            }

            $this->output->success(': ');

            $value = trim(fgets(STDIN));

            if (empty($value) === true) {
                $this->input->setArgumentValue($arg, $this->input->getDefinition()->getArgumentDefinition($arg)->defaultValue);
                continue;
            }

            $this->input->setArgumentValue($arg, $value);
        }
    }
}
