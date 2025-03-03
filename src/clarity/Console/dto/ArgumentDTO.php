<?php

declare(strict_types=1);

namespace framework\clarity\Console\dto;

final class ArgumentDTO
{
    /**
     * @param string|null $description
     * @param bool|null $required
     * @param string|null $defaultValue
     */
    public function __construct(
        public ?string $description = null,
        public ?bool   $required  = null,
        public ?string $defaultValue = null
    ) {}
}