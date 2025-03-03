<?php

namespace framework\clarity\Logger;

use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Container\interfaces\ParameterStorageInterface;
use framework\clarity\EventDispatcher\interfaces\EventDispatcherInterface;
use framework\clarity\Kernel\KernelEvents;
use framework\clarity\Logger\interfaces\DebugTagStorageInterface;
use framework\clarity\Logger\observers\TagUpdatePreventListener;
use InvalidArgumentException;
use RuntimeException;

/**
 * Класс DebugTagGenerator отвечает за генерацию и управление тегом отладки, который используется для
 * идентификации запросов и их контекста в процессе выполнения приложения.
 *
 * @package clarity/Logger
 */
readonly class DebugTagGenerator
{
    /**
     * @param EventDispatcherInterface $dispatcher
     * @param ContainerInterface $container
     * @param ParameterStorageInterface $storage
     */
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private ContainerInterface $container,
        private ParameterStorageInterface $storage,
    ) {}

    public function init(): void
    {
        if (empty($this->storage->get('app.name')) === true) {
            throw new InvalidArgumentException('Не задано имя приложения. Внесите доработку в конфигурацию приложения.');
        }

        $this->dispatcher->attach(KernelEvents::KERNEL_RESPONSE, TagUpdatePreventListener::class);

        $this->dispatcher->attach(KernelEvents::KERNEL_REQUEST, TagUpdatePreventListener::class);

        $this->updateTag();
    }

    /**
     * @return void
     */
    public function updateTag(): void
    {
        if ($this->container->get(TagUpdatePreventListener::class)->isTagUpdatePrevented() === true) {
            throw new RuntimeException('Обновление тега запрещено во время выполнения ядра обработки HTTP-запросов.');
        }

        $storage = $this->container->get(DebugTagStorageInterface::class);

        $headers = getallheaders();

        if (isset($headers['X-Debug-Tag']) === true) {

            $storage->setTag($headers['X-Debug-Tag']);

            return;
        }

        $key = 'x-debug-tag-' . $this->storage->get('app.name') .  '-' . uniqid();

        $key .= '-' . gethostname() . '-' . time();

        $storage->setTag(md5($key));
    }
}