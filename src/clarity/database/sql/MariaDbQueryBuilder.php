<?php

declare(strict_types=1);

namespace framework\clarity\database\sql;

use framework\clarity\database\interfaces\MysqlQueryBuilderInterface;
use InvalidArgumentException;

class MariaDbQueryBuilder implements MysqlQueryBuilderInterface
{
    private string $select = '';
    private string $from = '';
    private string $where = '';
    private array $joins = [];
    private string $orderBy = '';
    private string $limit = '';
    private string $offset = '';
    private array $bindings = [];

    /**
     * @param array|string ...$fields
     * @return $this
     */
    public function select(array|string ...$fields): static
    {
        $fields = is_array($fields[0]) ? $fields[0] : $fields;

        $escapedFields = array_map(function ($field) {
            if (stripos($field, ' AS ') !== false) {
                list($original, $alias) = explode(' AS ', $field, 2);
                return $this->escapeField(trim($original)) . ' AS ' . $this->escapeField(trim($alias));
            }
            return $this->escapeField($field);
        }, $fields);

        $this->select = 'SELECT ' . implode(', ', $escapedFields);
        return $this;
    }

    public function from(array|string $resource): static
    {
        $resource = is_array($resource) ? $resource : [$resource];

        $escapedResources = array_map(function ($item) {
            return $this->escapeField($item);
        }, $resource);

        $this->from = 'FROM ' . implode(', ', $escapedResources);

        return $this;
    }

    public function where(array $condition): static
    {
        $whereParts = [];
        foreach ($condition as $key => $value) {
            $param = 'where_' . count($this->bindings);
            $escapedKey = $this->escapeField($key);
            $whereParts[] = "$escapedKey = :$param";
            $this->bindings[$param] = $value;
        }

        if (empty($whereParts) === false) {
            $this->where = 'WHERE ' . implode(' AND ', $whereParts);
        }

        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $params = [];
        $escapedColumn = $this->escapeField($column);

        foreach ($values as $value) {
            $param = 'where_in_' . count($this->bindings);
            $params[] = ":$param";
            $this->bindings[$param] = $value;
        }

        $this->where = 'WHERE ' . $escapedColumn . ' IN (' . implode(', ', $params) . ')';
        return $this;
    }

    public function join(string $type, string|array $resource, string $on): static
    {
        $type = strtoupper($type);
        if (in_array($type, ['INNER', 'LEFT', 'RIGHT', 'FULL']) === false) {
            throw new InvalidArgumentException("Некорректный тип JOIN'а");
        }

        $resource = is_array($resource) ? $resource : [$resource];
        $escapedResources = array_map(function ($item) {
            return $this->escapeField($item);
        }, $resource);

        $this->joins[] = $type . ' JOIN ' . implode(', ', $escapedResources) . ' ON ' . $on;
        return $this;
    }

    public function orderBy(array $columns): static
    {
        $orderParts = [];

        foreach ($columns as $column => $direction) {
            $columnName = '';
            $dir = 'ASC';
            $isNumericArray = is_int($column);
            $isStringDirection = is_string($direction);

            if ($isNumericArray && $isStringDirection) {
                $parts = preg_split('/\s+/', trim($direction), 2);
                $columnName = $parts[0];
                $dir = isset($parts[1]) ? strtoupper($parts[1]) : 'ASC';
            }

            if (false === $isNumericArray) {
                $columnName = $column;
                $dir = $isStringDirection ? strtoupper(trim($direction)) : 'ASC';
            }

            $isEmptyColumnName = empty($columnName);
            if ($isEmptyColumnName) {
                throw new InvalidArgumentException('Имя колонны должно быть заполнено');
            }

            $isValidDirection = in_array($dir, ['ASC', 'DESC']);
            if (false === $isValidDirection) {
                throw new InvalidArgumentException('Некорректный формат распределения');
            }

            $escapedColumn = $this->escapeField($columnName);
            $orderParts[] = "$escapedColumn $dir";
        }

        $hasOrderParts = empty($orderParts) === false;
        if ($hasOrderParts) {
            $this->orderBy = 'ORDER BY ' . implode(', ', $orderParts);
        }

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = 'LIMIT ' . $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = 'OFFSET ' . $offset;
        return $this;
    }

    /**
     * @return StatementParameters
     */
    public function getStatement(): StatementParameters
    {
        $sqlParts = [
            $this->select,
            $this->from,
            implode(' ', $this->joins),
            $this->where,
            $this->orderBy,
            $this->limit,
            $this->offset
        ];

        $sql = implode(' ', array_filter($sqlParts, fn($part) => empty($part) === false));

        return new StatementParameters($sql, $this->bindings);
    }

    /**
     * @param $field
     * @return string
     */
    private function escapeField($field): string
    {
        if ($field === '*') {
            return $field;
        }

        if (preg_match('/^[a-z]+\(.*\)$/i', $field)) {
            return $field;
        }

        if (str_contains($field, '.')) {
            $parts = explode('.', $field);
            return '`' . implode('`.`', array_map('trim', $parts)) . '`';
        }

        return '`' . str_replace('`', '``', $field) . '`';
    }

    /**
     * @return string
     */
    public function getRawSql(): string
    {
        $statement = $this->getStatement();
        $sql = $statement->sql;
        $bindings = $statement->bindings;

        foreach ($bindings as $param => $value) {
            $escapedValue = is_string($value) === true ? "'" . str_replace("'", "''", $value) . "'"
                : (is_null($value) === true ? 'NULL' : $value);

            $sql = str_replace(':' . $param, $escapedValue, $sql);
        }

        return $sql;
    }
}
