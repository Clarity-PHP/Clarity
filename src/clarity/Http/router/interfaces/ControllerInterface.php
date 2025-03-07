<?php

declare(strict_types=1);

namespace framework\clarity\Http\router\interfaces;

use framework\clarity\Http\interfaces\ResponseInterface;

interface ControllerInterface
{
    /**
     * @param string $view
     * @param array $params
     * @return ResponseInterface
     */
    public function render(string $view, array $params = []): ResponseInterface;
}