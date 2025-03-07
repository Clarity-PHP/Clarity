<?php

declare(strict_types=1);

namespace framework\clarity\Http\router\interfaces;

interface MiddlewareAssignable
{
    /**
     * Добавление мидлвеера
     *
     * @param  callable|string $middleware коллбек функция или неймспейс класса мидлвеера
     * @return void
     */
    public function addMiddleware(callable|string $middleware): MiddlewareAssignable;
}