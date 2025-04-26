<?php

declare(strict_types=1);

namespace framework\clarity\database\interfaces;

interface QueryBuilderInterface
{
    function select(array|string ...$fields): static;

    function from(array|string $resource): static;

    function where(array $condition): static;

    function whereIn(string $column, array $values): static;

    function join(string $type, string|array $resource, string $on): static;

    function orderBy(array $columns): static;

    function limit(int $limit): static;

    function offset(int $offset): static;
}
