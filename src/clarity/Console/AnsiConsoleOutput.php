<?php

declare(strict_types=1);

namespace framework\clarity\Console;

use framework\clarity\Console\contracts\ConsoleOutputInterface;
use framework\clarity\Console\enums\ConsoleColorsEnum;
use RuntimeException;

/**
 * Обработка вывода в терминал консоли
 */
final class AnsiConsoleOutput implements ConsoleOutputInterface
{
    public function __construct(
        private $stdOut = STDOUT,
        private $stdErr = STDERR,
    ) {}

    /**
     * Создать строку вывода в формате ANSI
     *
     * @param string $message сообщение вывода
     * @param array $format формат вывода (цвет, стиль)
     * @return string
     */
    private function createAnsiLine(string $message, array $format = []): string
    {
        $code = implode(';', $format);

        return "\033[0m" . ($code !== '' ? "\033[" . $code . 'm' : '') . $message . "\033[0m";
    }

    /**
     * @param string $text
     * @return string
     */
    private function removeAnsiCodes(string $text): string
    {
        return preg_replace('/\033\[[0-9;]*m/', '', $text);
    }

    /**
     * Запись строку вывода в поток вывода
     *
     * @param string $message сообщение вывода
     * @param array $format формат вывода (цвет, стиль)
     * @return string
     */
    public function stdout(string $message): void
    {
        $args = func_get_args();
        array_shift($args);

        $line = $this->createAnsiLine($message, $args);

        if ($this->stdOut !== STDOUT) {
            $line = $this->removeAnsiCodes($line);
        }

        fwrite($this->stdOut, $line);
    }

    /**
     * Запись строку вывода в поток вывода ошибок
     *
     * @param string $message сообщение вывода
     * @param array $format формат вывода (цвет, стиль)
     * @return string
     */
    public function stdErr(string $message): void
    {
        $args = func_get_args();
        array_shift($args);

        $line = $this->createAnsiLine($message, $args);

        fwrite($this->stdErr, $line);
    }

    /**
     * Вывод сообщения об успехе операции
     *
     * @param string $message сообщение вывода
     * @param array $format формат вывода (цвет, стиль)
     * @return string
     */
    public function success(string $message): void
    {
        $this->stdout($message, ConsoleColorsEnum::FG_GREEN->value);
    }

    /**
     * @param string $message
     * @return void
     */
    public function error(string $message): void
    {
        $this->stdout($message, ConsoleColorsEnum::FG_RED->value);
    }

    /**
     * Вывод информационного сообщения об операци
     *
     * @param string $message сообщение вывода
     * @param array $format формат вывода (цвет, стиль)
     * @return string
     */
    public function info(string $message): void
    {
        $this->stdout($message, ConsoleColorsEnum::FG_CYAN->value);
    }

    /**
     * Вывод предупреждающего сообщения об операци
     *
     * @param string $message сообщение вывода
     * @param array $format формат вывода (цвет, стиль)
     * @return string
     */
    public function warning(string $message): void
    {
        $this->stdout($message, ConsoleColorsEnum::FG_YELLOW->value);
    }

    /**
     * Создание массива строк одинакового контента
     *
     * @param int $count количество повторений строки
     * @return void
     */
    public function writeLn(int $count = 1): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->stdout("\n");
        }
    }

    /**
     * Переопределение ресурса вывода
     *
     * @param resource $resource ресурс вывода
     * @return void
     */
    public function setStdOut($resource): void
    {
        if (is_resource($this->stdOut) === true) {
            fclose($this->stdOut);
        }

        $this->stdOut = fopen($resource, 'ab');
    }

    /**
     * Переопределение ресурса вывода ошибок
     *
     * @param resource $resource ресурс вывода
     * @return void
     */
    public function setStdErr($resource): void
    {
        if (is_resource($this->stdErr) === true) {
            fclose($this->stdErr);
        }

        $this->stdErr = fopen($resource, 'ab');
    }

    public function detach($resource = '/dev/null'): void
    {
        $pid = pcntl_fork();

        if ($pid < 0) {
            throw new RuntimeException("Ошибка при форкинге процесса");
        }

        if ($pid > 0) {
            exit(0);
        }

        if (posix_setsid() < 0) {
            throw new RuntimeException("Не удалось создать новую сессию процесса");
        }

        $pid = pcntl_fork();

        if ($pid < 0) {
            throw new RuntimeException("Ошибка при втором форкинге процесса");
        }

        if ($pid > 0) {
            exit(0);
        }

        $logFile = fopen($resource, 'ab');
        if ($logFile === false) {
            throw new RuntimeException("Ошибка открытия файла $resource");
        }

        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);

        $this->setStdOut($resource);
        $this->setStdErr($resource);

        fwrite($logFile, "Процесс успешно запущен в фоне (PID: " . getmypid() . ")\n");

        sleep(2);

        fwrite($logFile, "Фоновая задача завершена.\n");

        fclose($logFile);
    }
}
