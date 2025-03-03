<?php

namespace framework\clarity\Logger;

use framework\clarity\Logger\interfaces\LoggerInterface;

/**
 * Абстрактный класс AbstractLogger реализует базовую логику для логирования сообщений различных уровней
 * с возможностью форматирования сообщений и записи в лог. Этот класс предоставляет общие методы для всех
 * логеров, включая методы для записи сообщений критических ошибок, ошибок, предупреждений, информационных
 * сообщений и отладочных данных.

 * Абстрактные методы:
 * - `formatMessage(string $level, mixed $message): string`: Форматирует сообщение для записи в лог.
 * - `writeLog(string $log): void`: Записывает форматированное сообщение в лог.
 *
 * @package clarity/Logger
 */
abstract class AbstractLogger implements LoggerInterface
{
    /**
     * @var array
     */
    public array $levels {
        get {
            return $this->levels;
        }
        set {
            $this->levels = $value;
        }
    }

    /**
     * @var string
     */
    public string $channel {
        set {
            $this->channel = $value;
        }
        get {
            return $this->channel;
        }
    }

    /**
     * @param array $levels
     * @param string $channel
     */
    public function __construct(array $levels, string $channel)
    {
        $this->levels = $levels;

        $this->channel = $channel;
    }

    /**
     * @param mixed $message сообщение
     * @return void
     */
    public function critical(mixed $message): void
    {
        $this->log(LogLevel::FATAL->value, $message);
    }

    /**
     * @param mixed $message сообщение
     * @return void
     */
    public function error(mixed $message): void
    {
        $this->log(LogLevel::ERROR->value, $message);
    }

    /**
     * @param mixed $message сообщение
     * @return void
     */
    public function warning(mixed $message): void
    {
        $this->log(LogLevel::WARNING->value, $message);
    }

    /**
     * @param mixed $message сообщение
     * @return void
     */
    public function info(mixed $message): void
    {
        $this->log(LogLevel::INFO->value, $message);
    }

    /**
     * @param mixed $message сообщение
     * @return void
     */
    public function debug(mixed $message): void
    {
        $this->log(LogLevel::DEBUG->value, $message);
    }

    /**
     * @param string $level
     * @param mixed $message
     * @return string
     */
    abstract protected function formatMessage(string $level, mixed $message): string;

    /**
     * @param string $log
     * @return void
     */
    abstract protected function writeLog(string $log): void;

    /**
     * @param string $level
     * @param mixed $message
     * @return void
     */
    private function log(string $level, mixed $message): void
    {
        $log = $this->formatMessage($level, $message);

        $this->writeLog($log);
    }
}