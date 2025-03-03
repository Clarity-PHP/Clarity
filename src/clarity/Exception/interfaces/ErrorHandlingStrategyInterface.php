<?php

namespace framework\clarity\Exception\interfaces;

use Throwable;

interface ErrorHandlingStrategyInterface
{
    /**
     * @param Throwable $e
     * @return string
     */
    public function handle(Throwable $e): string;
}