<?php

namespace framework\clarity\EventDispatcher;

abstract class AbstractMessage
{
    private mixed $message;

    private string $event;

    /**
     * @param string $message
     */
    public function __construct(mixed $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage(): mixed
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getEventName(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     * @return void
     */
    public function setEventName(string $event): void
    {
        $this->event = $event;
    }
}