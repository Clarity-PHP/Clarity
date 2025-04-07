<?php

declare(strict_types=1);

namespace framework\clarity\database\file;

final readonly class StatementParameters
{
    public function __construct(
        public string $resource,
        public array $selectFields = [],
        public array $whereClause = [],
        public array $orderByClause = [],
        public ?int $limit = null,
        public ?int $offset = null,
    ) {}
}
