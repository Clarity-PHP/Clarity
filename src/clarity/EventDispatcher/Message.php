<?php

namespace framework\clarity\EventDispatcher;

class Message extends AbstractMessage
{
    /**
     * @param mixed $content
     */
    public function __construct(mixed $content)
    {
        parent::__construct($content);
    }
}