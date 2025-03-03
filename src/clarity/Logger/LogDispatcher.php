<?php

namespace framework\clarity\Logger;

use framework\clarity\Container\DIContainer;
use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\EventDispatcher\interfaces\EventDispatcherInterface;
use framework\clarity\EventDispatcher\interfaces\ObserverFactoryInterface;
use framework\clarity\Logger\interfaces\LoggerInterface;
use framework\clarity\Logger\observers\ContextObserver;
use RuntimeException;

/**
 * Класс LogDispatcher отвечает за управление логированием в приложении. Он управляет несколькими логгерами,
 * обрабатывает контексты сообщений и выполняет логирование на основе конфигурации.
 *
 * @package clarity/Logger
 */
final class LogDispatcher
{
    private bool $isBooted = false;
    private LogContext $storage;

    /**
     * @param array $loggers
     * @param DIContainer $di
     * @param ObserverFactoryInterface $observerFactory
     * @param DebugTagGenerator $generator
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        private array $loggers,
        private readonly ContainerInterface $di,
        private readonly ObserverFactoryInterface $observerFactory,
        private readonly DebugTagGenerator $generator,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        $this->generator->init();
    }

    /**
     * @param string $channel
     * @return LoggerInterface
     */
    public function getLogger(string $channel): LoggerInterface
    {
        $this->boot();

        if (isset($this->loggers[$channel]) === false || isset($this->loggers[$channel]['instance']) === false) {
            throw new RuntimeException("Logger for channel '{$channel}' not found.");
        }

        return $this->loggers[$channel]['instance'];
    }

    /**
     * @param string $level
     * @param mixed $message
     * @param string $category
     * @return void
     */
    public function log(string $level, mixed $message, string $category = ''): void
    {
        $preparedMessage = $this->prepareMessage($level, $message, $category);

        foreach ($this->loggers as $logger) {
            $logger->$level($preparedMessage);
        }
    }

    /**
     * @param array $message
     * @return array
     */
    public function handleContext(mixed $message): array
    {
        $handler = new LogContextHandler();

        $storage = clone $this->storage;

        $storage->data = $message;

        $handler->handle($storage);

        return (array)$storage;
    }

    /**
     * @return void
     */
    private function boot(): void
    {
        if ($this->isBooted === true) {
            return;
        }

        $this->storage = $this->di->get(LogContext::class);

        $this->observerFactory->register(ContextObserver::class, new ContextObserver($this->storage));

        $this->dispatcher->attach(LogEvents::ATTACH_CONTEXT, ContextObserver::class);
        $this->dispatcher->attach(LogEvents::DETACH_CONTEXT, ContextObserver::class);
        $this->dispatcher->attach(LogEvents::ATTACH_EXTRAS, ContextObserver::class);
        $this->dispatcher->attach(LogEvents::FLUSH_EXTRAS, ContextObserver::class);
        $this->dispatcher->attach(LogEvents::FLUSH_CONTEXT, ContextObserver::class);

        foreach ($this->loggers as $class => $loggerConfig) {

            $instance = $this->di->build($class, [
                'levels' => $loggerConfig['levels'] ?? [],
                'channel' => $loggerConfig['channel'] ?? 'php://stdout',
            ]);

            if ($instance instanceof LoggerInterface === false) {
                throw new RuntimeException("Class {$class} does not implement LoggerInterface.");
            }

            $this->loggers[$class]['instance'] = $instance;
        }

        $this->isBooted = true;
    }

    /**
     * @param string $level
     * @param mixed $message
     * @param string $category
     * @return array
     */
    private function prepareMessage(string $level, mixed $message, string $category): array
    {
        return [
            LogLevel::tryFrom($level)?->toInt() ?? '-1',
            LogLevel::tryFrom($level)?->value ?? 'unknown',
            time(),
            $message,
            $category,
        ];
    }
}


