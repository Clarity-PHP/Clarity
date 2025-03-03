<?php

namespace framework\clarity\Exception\strategies;

use framework\clarity\Exception\interfaces\ErrorHandlingStrategyInterface;
use Throwable;

class ConsoleErrorHandlingStrategy implements ErrorHandlingStrategyInterface
{
    /**
     * @param Throwable $e
     * @return string
     */
    public function handle(Throwable $e): string
    {
        $trace = $e->getTraceAsString();

        $redBackground = "\e[41m";
        $whiteText = "\e[97m";
        $bold = "\e[1m";
        $reset = "\e[0m";

        return sprintf(
            "%sОшибка: %s%s\n%sФайл: %s%s\n%sСтрока: %d%s\n%sТрейс:%s\n%s%s%s",
            $bold . $whiteText, $e->getMessage(), $reset,
            $bold . $whiteText, $e->getFile(), $reset,
            $bold . $whiteText, $e->getLine(), $reset,
            $bold . $whiteText, $reset,
            $redBackground . $whiteText, $trace, $reset
        );
    }
}