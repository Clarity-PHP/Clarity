<?php

namespace framework\clarity\Kernel\interfaces;

use framework\clarity\Http\interfaces\ResponseInterface;
use framework\clarity\Http\interfaces\ServerRequestInterface;

interface HttpKernelInterface extends KernelInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(
        framework\clarity\Http\interfaces\ServerRequestInterface $request): framework\clarity\Http\interfaces\ResponseInterface;
}