<?php

namespace framework\clarity\Console\contracts;

interface ConsoleCommandInterface
{
    /**
     * Логика команды
     *
     * @return void
     */
    public function execute(): void;

    /**
     * @return string
     */
    public static function getSignature() : string;

    /**
     * @return string
     */
    public static function getDescription() : string;
}