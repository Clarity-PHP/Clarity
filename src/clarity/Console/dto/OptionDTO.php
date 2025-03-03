<?php

declare(strict_types=1);

namespace framework\clarity\Console\dto;

final class OptionDTO
{
    /**
     * @param string|null $description
     */
    public function __construct(
        public ?string $description = null,
    ) {}
}