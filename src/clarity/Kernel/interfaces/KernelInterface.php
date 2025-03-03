<?php

namespace framework\clarity\Kernel\interfaces;

interface KernelInterface
{
    /**
     * @return void
     */
    public function boot(): void;

    /**
     * @return void
     */
    public function shutdown(): void;
}