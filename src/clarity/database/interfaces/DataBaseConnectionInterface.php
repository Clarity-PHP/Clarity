<?php

declare(strict_types=1);

namespace framework\clarity\database\interfaces;

interface DataBaseConnectionInterface
{
    public function select(QueryBuilderInterface $query): array;

    public function selectOne(QueryBuilderInterface $query): null|array;

    public function selectColumn(QueryBuilderInterface $query): array;

    public function selectScalar(QueryBuilderInterface $query): mixed;

    public function update(string $resource, array $data, array $condition): int;

    public function insert(string $resource, array $data): int;

    public function delete(string $resource, array $condition): int;

    public function getLastInsertId(): string;
}
