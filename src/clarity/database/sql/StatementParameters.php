<?php

declare(strict_types=1);

namespace framework\clarity\database\sql;

final readonly class StatementParameters
{
    public function __construct(
        public string $sql,
        public array $bindings
    ) {}
}
