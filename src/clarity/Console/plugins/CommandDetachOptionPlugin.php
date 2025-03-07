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

final class CommandDetachOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private string $optionName;

    /**
     * @param ConsoleInputInterface $input
     * @param ConsoleOutputInterface $output
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        $this->optionName = 'detach';
        $this->input->addDefaultOption($this->optionName, 'Плагин перевода в detach');

        $this->dispatcher->attach(ConsoleEventsEnum::CONSOLE_INPUT_AFTER_PARSE->name, $this::class);
    }

    /**
     * @param Message $message
     * @return void
     */
    public function handle(Message $message): void
    {
        if ($this->input->hasOption($this->optionName) === false) {
            return;
        }

        $this->output->detach('/var/log/clarity.log');
    }
}