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
 * Плагин записи вывода результата выполнения команды в файл
 */
class CommandSavePlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private string $optionName;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ConsoleKernelInterface $kernel,
    )
    {
        $this->optionName = 'save-file';
        $this->input->addDefaultOption($this->optionName, 'Плагин записи вывода результата выполнения команды в файл ');

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

        if (isset($this->input->getOption($this->optionName)[0]) === false) {
            throw new InvalidArgumentException('Не указан путь сохранения файла');
        }

        $this->output->setStdOut($this->input->getOption($this->optionName)[0]);
    }
}
