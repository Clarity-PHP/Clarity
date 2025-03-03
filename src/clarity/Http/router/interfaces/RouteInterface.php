<?php

declare(strict_types=1);

namespace framework\clarity\Http\router\interfaces;

interface RouteInterface
{
    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string
     */
    public function getHandler(): string;

    /**
     * @return string
     */
    public function getAction(): string;

    /**
     * @return array
     */
    public function getParams(): array;
}