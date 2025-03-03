<?php

namespace framework\clarity\Logger\interfaces;

interface DebugTagStorageInterface
{
    /**
     * @return string
     */
    public function getTag(): string;

    /**
     * @param string $tag
     * @return void
     */
    public function setTag(string $tag): void;
}