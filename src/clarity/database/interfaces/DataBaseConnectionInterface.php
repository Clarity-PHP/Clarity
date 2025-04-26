<?php

declare(strict_types=1);

namespace framework\clarity\database\interfaces;



interface DataBaseConnectionInterface
{
    function select(QueryBuilderInterface $query): array;

    function selectOne(QueryBuilderInterface $query): null|array;

    function selectColumn(QueryBuilderInterface $query): array;

    function selectScalar(QueryBuilderInterface $query): mixed;

    function update(string $resource, array $data, array $condition): int;

    function insert(string $resource, array $data): int;

    function delete(string $resource, array $condition): int;

    function getLastInsertId(): string;
}
