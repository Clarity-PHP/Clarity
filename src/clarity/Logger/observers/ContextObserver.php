<?php

namespace framework\clarity\Logger\observers;

use framework\clarity\EventDispatcher\interfaces\ObserverInterface;
use framework\clarity\EventDispatcher\Message;
use framework\clarity\Logger\LogContext;
use framework\clarity\Logger\LogEvents;
use InvalidArgumentException;

class ContextObserver implements ObserverInterface
{
    private LogContext $storage;

    /**
     * @param LogContext $storage
     */
    public function __construct(LogContext $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param Message $message
     * @return void
     */
    public function handle(Message $message): void
    {
        match ($message->getEventName()) {
            LogEvents::ATTACH_CONTEXT => $this->attachContext($message),
            LogEvents::DETACH_CONTEXT => $this->detachContext($message),
            LogEvents::FLUSH_CONTEXT => $this->flushContext(),
            LogEvents::ATTACH_EXTRAS => $this->attachExtras($message),
            LogEvents::FLUSH_EXTRAS => $this->flushExtras(),
            default => throw new InvalidArgumentException("Unknown event: {$message->getEventName()}"),
        };
    }

    /**
     * @param Message $message
     * @return void
     */
    private function attachContext(Message $message): void
    {
        if (empty($this->storage->result['context']) === true) {
            $this->storage->result['context'] = $message->getMessage();

            return;
        }

        $this->storage->result['context'] .= ':' . $message->getMessage();
    }

    /**
     * @param Message $message
     * @return void
     */
    private function detachContext(Message $message): void
    {
        if (isset($this->storage->result['context']) === true
            && str_contains($this->storage->result['context'], $message->getMessage()) === true
        ) {
            $this->storage->result['context'] = str_replace(':' . $message->getMessage(), '', $this->storage->result['context']);

            if ($this->storage->result['context'][0] === ':') {
                $this->storage->result['context'] = substr($this->storage->result['context'], 1);
            }
        }
    }

    /**
     * @return void
     */
    private function flushContext(): void
    {
        $this->storage->result['context'] = '';
    }

    /**
     * @param Message $message
     * @return void
     */
    private function attachExtras(Message $message): void
    {
        if (empty($this->storage->result['extras']) === true) {
            $this->storage->result['extras'] = $message->getMessage();
        }
    }

    /**
     * @return void
     */
    private function flushExtras(): void
    {
        $this->storage->result['extras'] = null;
    }
}