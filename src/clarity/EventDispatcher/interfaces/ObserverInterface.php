<?php

declare(strict_types=1);

namespace framework\clarity\EventDispatcher\interfaces;

use framework\clarity\EventDispatcher\Message;

interface ObserverInterface
{
    public function handle(Message $message): void;
}