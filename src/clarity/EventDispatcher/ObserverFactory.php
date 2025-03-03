<?php

namespace framework\clarity\EventDispatcher;

use framework\clarity\EventDispatcher\interfaces\ObserverFactoryInterface;
use framework\clarity\EventDispatcher\interfaces\ObserverInterface;
use InvalidArgumentException;

class ObserverFactory implements ObserverFactoryInterface
{
    private array $instances = [];

    /**
     * @param string $class
     * @param ObserverInterface $observer
     * @return void
     */
    public function register(string $class, ObserverInterface $observer): void
    {
        $this->instances[$class] = $observer;
    }

    /**
     * @param string $observerClass
     * @return ObserverInterface
     */
    public function create(string $observerClass): ObserverInterface
    {
        if (isset($this->instances[$observerClass]) === false) {
            throw new InvalidArgumentException("Observer class $observerClass is not registered.");
        }

        return $this->instances[$observerClass];
    }
}