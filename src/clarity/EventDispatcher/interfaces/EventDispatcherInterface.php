<?php
declare(strict_types=1);

namespace framework\clarity\EventDispatcher\interfaces;

use framework\clarity\EventDispatcher\Message;

interface EventDispatcherInterface
{
    /**
     * @param array $config
    */
    public function configure(array $config): void;

    /**
     * @param string $eventName
     * @param string $observer
     * @return void
     */
    public function attach(string $eventName, string $observer): void;

    /**
     * @param string $eventName
     * @return void
     */
    public function detach(string $eventName): void;

    /**
     * @param string $eventName
     * @param Message $message
     * @return void
     */
    public function trigger(string $eventName, Message $message): void;
}