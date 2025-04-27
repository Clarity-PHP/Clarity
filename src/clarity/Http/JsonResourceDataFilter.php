<?php

declare(strict_types=1);

namespace framework\clarity\Http;

use framework\clarity\Http\enum\FilterOperator;
use framework\clarity\Http\interfaces\ResourceDataFilterInterface;
use framework\clarity\database\interfaces\DataBaseConnectionInterface;
use framework\clarity\database\interfaces\QueryBuilderInterface;
use InvalidArgumentException;
use RuntimeException;

final class JsonResourceDataFilter implements ResourceDataFilterInterface
{
    private string $resourceName;
    private array  $accessibleFields  = [];
    private array  $accessibleFilters = [];

    /**
     * @param DataBaseConnectionInterface $connection
     * @param QueryBuilderInterface $builder
     */
    public function __construct(
        private readonly DataBaseConnectionInterface $connection,
        private readonly QueryBuilderInterface       $builder
    ) {}

    /**
     * @param string $name
     * @return $this
     */
    public function setResourceName(string $name): static
    {
        if ($name === '') {
            throw new InvalidArgumentException('Resource name must be non-empty.');
        }

        $this->resourceName = $name;

        return $this;
    }

    /**
     * @param array $fieldNames
     * @return $this
     */
    public function setAccessibleFields(array $fieldNames): static
    {
        if (empty($fieldNames) === true) {
            throw new InvalidArgumentException('Accessible fields list cannot be empty.');
        }

        $this->accessibleFields = $fieldNames;

        return $this;
    }

    /**
     * @param array $filterNames
     * @return $this
     */
    public function setAccessibleFilters(array $filterNames): static
    {
        if (empty($filterNames) === true) {
            throw new InvalidArgumentException('Accessible filters list cannot be empty.');
        }

        $this->accessibleFilters = $filterNames;

        return $this;
    }

    /**
     * @param array $condition
     * @return array
     */
    public function filterAll(array $condition): array
    {
        $this->assertResourceName();

        $fields  = $condition['fields'] ?? [];

        $filters = $condition['filter'] ?? [];

        foreach ($fields as $f) {
            if (in_array($f, $this->accessibleFields, true) === false) {
                throw new InvalidArgumentException("Field '{$f}' is not allowed.");
            }
        }
        foreach (array_keys($filters) as $column) {
            if (in_array($column, $this->accessibleFilters, true) === false) {
                throw new InvalidArgumentException("Filter on column '{$column}' is not allowed.");
            }
        }

        $qb = $this->builder
            ->select($fields ?: $this->accessibleFields)
            ->from($this->resourceName);

        foreach ($filters as $column => $ops) {
            foreach ($ops as $opString => $value) {
                $operator = FilterOperator::from($opString);
                $operator->apply($qb, $column, $value);
            }
        }

        return $this->connection->select($qb->getStatement());
    }

    /**
     * @param array $condition
     * @return array
     */
    public function filterOne(array $condition): array
    {
        $rows = $this->filterAll($condition);

        return $rows[0] ?? [];
    }

    /**
     * @return void
     */
    private function assertResourceName(): void
    {
        if (empty($this->resourceName) === true) {
            throw new RuntimeException('Resource name has not been set.');
        }
    }
}
