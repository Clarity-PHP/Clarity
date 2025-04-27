<?php

declare(strict_types=1);

namespace framework\clarity\database\file;

use framework\clarity\database\interfaces\FileQueryBuilderInterface;

class JsonQueryBuilder implements FileQueryBuilderInterface
{
    public string  $resource;
    public ?array  $selectFields   = null;
    public array   $whereClause    = [];
    public array   $whereInClauses = [];
    public array   $joinClauses    = [];
    public array   $orderByClause  = [];
    public ?int    $limit          = null;
    public ?int    $offset         = null;

    /**
     * @param array|string ...$fields
     * @return $this
     */
    public function select(array|string ...$fields): static
    {
        $flat = [];

        foreach ($fields as $f) {
            if (is_array($f) === true) {
                $flat = array_merge($flat, $f);

                continue;
            }

            $flat[] = $f;

        }
        $this->selectFields = $flat;

        return $this;
    }

    /**
     * @param array|string $resource
     * @return $this
     */
    public function from(array|string $resource): static
    {
        $this->resource = is_array($resource)
            ? implode('/', $resource)
            : $resource;
        return $this;
    }

    /**
     * @param array $condition
     * @return $this
     */
    public function where(array $condition): static
    {
        $this->whereClause[] = $condition;

        return $this;
    }

    /**
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function whereIn(string $column, array $values): static
    {
        $this->whereInClauses[] = [$column => $values];

        return $this;
    }

    /**
     * @param string $type
     * @param string|array $resource
     * @param string $on
     * @return $this
     */
    public function join(string $type, string|array $resource, string $on): static
    {
        $res = is_array($resource)
            ? implode('/', $resource)
            : $resource;

        $this->joinClauses[] = [
            'type'     => $type,
            'resource' => $res,
            'on'       => $on,
        ];

        return $this;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function orderBy(array $columns): static
    {
        $this->orderByClause = $columns;

        return $this;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return StatementParameters
     */
    public function getStatement(): StatementParameters
    {
        $combinedWheres = array_merge(
            $this->whereClause,
            $this->whereInClauses
        );

        return new StatementParameters(
            resource:      $this->resource,
            selectFields:  $this->selectFields,
            whereClause:   $combinedWheres,
            orderByClause: $this->orderByClause,
            limit:         $this->limit,
            offset:        $this->offset
        );
    }
}
