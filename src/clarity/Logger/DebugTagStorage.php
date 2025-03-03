<?php

declare(strict_types=1);

namespace framework\clarity\Logger;

use framework\clarity\Logger\interfaces\DebugTagStorageInterface;
use RuntimeException;

/**
 * Класс DebugTagStorage отвечает за хранение и управление тегом отладки, который используется для отслеживания
 * и идентификации различных операций в процессе логирования.
 *
 * @package clarity/Logger
 */
class DebugTagStorage implements DebugTagStorageInterface
{
    private ?string $tag = null;

    /**
     * @inheritDoc
     */
    public function getTag(): string
    {
        if ($this->tag === null) {
            throw new RuntimeException('Тег отладки не определен');
        }

        return $this->tag;
    }

    /**
     * @inheritDoc
     */
    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }
}