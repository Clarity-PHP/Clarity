<?php

namespace framework\clarity\Logger;

use framework\clarity\EventDispatcher\Message;

class LogMessage extends Message
{

    /**
     * @param string $message
     */
    public function __construct(private readonly string $message)
    {
        parent::__construct($message);
    }


    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}