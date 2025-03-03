<?php

declare (strict_types=1);

namespace framework\clarity\Logger;

use InvalidArgumentException;
use RuntimeException;

/**
 * Класс StdoutLogger отвечает за запись логов в стандартный поток вывода (stdout).
 * Логгер поддерживает возможность блокировки потока записи для предотвращения конфликтов
 * при записи данных из нескольких потоков. Логирование осуществляется в формате JSON.
 *
 * @package clarity/Logger
 */
class StdoutLogger extends AbstractLogger
{
    /**
     * @var resource
     */
    protected $fp;
    protected bool $openedFp = false;

    public bool $enableLocking = false;

    /**
     * @param LogDispatcher $dispatcher
     * @param array $levels
     * @param string $channel
     */
    public function __construct(
        private readonly LogDispatcher $dispatcher,
        array $levels,
        string $channel,
    ) {
        parent::__construct($levels, $channel);
    }

    /**
     * @inheritDoc
     */
    protected function formatMessage(string $level, mixed $message): string
    {
        if (in_array($level, $this->levels, true) === false) {
            throw new InvalidArgumentException(
                "Данный логгер не поддерживает текущий уровень логирования '{$level}', необходимо внести изменения в конфигурацию."
            );
        }

        $preparedMessage = [
            LogLevel::tryFrom($level)?->toInt() ?? '-1',
            LogLevel::tryFrom($level)?->value ?? 'unknown',
            time(),
            $message,
        ];


        $context = $this->dispatcher->handleContext($preparedMessage);

        return json_encode($context['result'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return mixed
     */
    protected function getFp(): mixed
    {
        if ($this->fp === null) {
            $this->fp = fopen($this->channel, 'w');

            if ($this->fp === false) {
                throw new InvalidArgumentException("Не удалось открыть '{$this->channel}' для записи.");
            }

            $this->openedFp = true;
        }

        return $this->fp;
    }

    /**
     * @return void
     */
    public function closeFp(): void
    {
        if ($this->openedFp === true && $this->fp !== null) {

            fclose($this->fp);

            $this->fp = null;

            $this->openedFp = false;
        }
    }

    /**
     * @inheritDoc
     */
    protected function writeLog(string $log): void
    {
        $fp = $this->getFp();

        if ($this->enableLocking === true) {
            flock($fp, LOCK_EX);
        }

        if (fwrite($fp, $log) === false) {

            $error = error_get_last();

            throw new RuntimeException("Не удалось записать в лог: {$error['message']}");
        }

        if ($this->enableLocking === true) {
            flock($fp, LOCK_UN);
        }

        $this->closeFp();
    }
}