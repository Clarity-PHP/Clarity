<?php

namespace framework\clarity\Logger\interfaces;

interface LoggerInterface
{
    /**
     * @var array
     */
    public array $levels {
        get;
    }

    /**
     * @var string
     */
    public string $channel {
        get;
    }

    /**
     * @param mixed $message
     * @return void
     */
    public function critical(mixed $message): void;

    /**
     * @param mixed $message
     * @return void
     */
    public function error(mixed $message): void;

    /**
     * @param mixed $message
     * @return void
     */
    public function warning(mixed $message): void;

    /**
     * @param mixed $message
     * @return void
     */
    public function debug(mixed $message): void;

    /**
     * @param mixed $message
     * @return void
     */
    public function info(mixed $message): void;
}