<?php

namespace framework\clarity\EventDispatcher;

use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\EventDispatcher\interfaces\EventDispatcherInterface;
use framework\clarity\EventDispatcher\interfaces\ObserverFactoryInterface;
use framework\clarity\EventDispatcher\interfaces\ObserverInterface;
use InvalidArgumentException;

class EventDispatcher implements EventDispatcherInterface
{
    private array $observers = [];

    /**
     * @param ObserverFactoryInterface $factory
     */
    public function __construct(
        private readonly ObserverFactoryInterface $factory,
        private readonly ContainerInterface $container,
    ) {}

    /**
     * @inheritDoc
     */
    public function configure(array $config): void
    {
        foreach ($config as $eventName => $observers) {
            foreach ($observers as $observerClass) {
                $observer = $this->factory->create($observerClass);

                $this->attach($eventName, $observer::class);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function attach(string $eventName, string $observer): void
    {
        if (class_exists($observer) === false) {
            throw new InvalidArgumentException("Observer class $observer does not exist.");
        }

        $this->observers[$eventName][] = $observer;
    }

    /**
     * @inheritDoc
     */
    public function detach(string $eventName): void
    {
        if (isset($this->observers[$eventName]) === true) {
            unset($this->observers[$eventName]);
        }
    }

    /**
     * @inheritDoc
     */
    public function trigger(string $eventName, Message $message): void
    {
        if (isset($this->observers[$eventName]) === false) {
            return;
        }

        foreach ($this->observers[$eventName] as $observer) {
            $observerInstance = $this->container->get($observer);

            if ($observerInstance instanceof ObserverInterface) {
                $message->setEventName($eventName);

                $observerInstance->handle($message);
            }
        }
    }
}