<?php

namespace framework\clarity\ComponentManager\interfaces;

interface ComponentInterface
{
    /**
     * @return void
     */
    public function boot(): void;
}