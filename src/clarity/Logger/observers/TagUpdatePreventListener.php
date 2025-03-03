<?php

namespace framework\clarity\Logger\observers;

use framework\clarity\EventDispatcher\interfaces\ObserverInterface;
use framework\clarity\EventDispatcher\Message;
use framework\clarity\Kernel\KernelEvents;

class TagUpdatePreventListener implements ObserverInterface
{
    private bool $tagUpdatePrevented = false;

    /**
     * @param Message $message
     */
    public function handle(Message $message): void
    {
        if ($message->getEventName() === KernelEvents::KERNEL_REQUEST || $message->getEventName() === KernelEvents::KERNEL_RESPONSE) {
            $this->tagUpdatePrevented = true;
        }
    }

    /**
     * @return bool
     */
    public function isTagUpdatePrevented(): bool
    {
        return $this->tagUpdatePrevented === true;
    }
}