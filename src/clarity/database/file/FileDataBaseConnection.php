<?php

declare(strict_types=1);

namespace framework\clarity\database\file;

use framework\clarity\database\interfaces\DataBaseConnectionInterface;
use framework\clarity\database\interfaces\QueryBuilderInterface;
use framework\clarity\Http\router\exceptions\HttpBadRequestException;
use framework\clarity\Http\router\exceptions\HttpNotFoundException;
use RuntimeException;
use JsonException;
use InvalidArgumentException;

class FileDataBaseConnection implements DataBaseConnectionInterface
{
    private string $directory;
    private ?int $lastInsertId = null;
    private const JSON_OPTIONS = JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;

    public function __construct(array $config)
    {
        $dir = $config['directory'] ?? '';

        if (is_dir($dir) === false) {
            throw new InvalidArgumentException(sprintf('Directory "%s" does not exist.', $dir));
        }

        $this->directory = rtrim($dir, DIRECTORY_SEPARATOR);
    }

    /**
     * @param QueryBuilderInterface $query
     * @return array
     * @throws JsonException
     */
    public function select(QueryBuilderInterface $query): array
    {
        $stmt = $query->getStatement();

        $rows = $this->readResource($stmt->resource);

        return $this->processQuery($rows, $stmt);
    }

    /**
     * @param QueryBuilderInterface $query
     * @return array|null
     * @throws JsonException
     */
    public function selectOne(QueryBuilderInterface $query): ?array
    {
       $first = $this->select($query)[0] ?? null;

       if ($first !== null && ! is_array($first)) {
          return ['value' => $first];
       }

       return $first;
    }

    /**
     * @param QueryBuilderInterface $query
     * @return array
     * @throws JsonException
     */
    public function selectColumn(QueryBuilderInterface $query): array
    {
        $rows = $this->select($query);

        $field = $query->getStatement()->selectFields[0] ?? null;

        if ($field === null) {
            throw new RuntimeException('No field specified for selectColumn.');
        }

        return array_column($rows, $field);
    }

    /**
     * @param QueryBuilderInterface $query
     * @return mixed
     * @throws JsonException
     */
    public function selectScalar(QueryBuilderInterface $query): mixed
    {
        return $this->selectColumn($query)[0] ?? null;
    }

    /**
     * @param string $resource
     * @param array $data
     * @param array $condition
     * @return int
     */
    public function update(string $resource, array $data, array $condition = []): int
    {
        $rows = $this->readResource($resource);

        $count = 0;

        foreach ($rows as &$row) {
            if ($this->matchesAll($row, $condition) === false) {
                continue;
            }

            $row = array_merge($row, $data);

            $count++;
        }

        unset($row);

        $this->writeResource($resource, $rows);

        return $count;
    }

    /**
     * @param string $resource
     * @param array $data
     * @return int
     * @throws HttpBadRequestException
     */
    public function insert(string $resource, array $data): int
    {
        $currentData = $this->readResource($resource);

        // Обработка простых массивов
        $isSimpleArray = array_values($currentData) === $currentData;
        if ($isSimpleArray === true) {
            $value = reset($data);

            if (is_scalar($value) === false) {
                throw new HttpBadRequestException("Only scalar values allowed in simple array '$resource'");
            }

            if (in_array($value, $currentData, true) === true) {
                throw new HttpBadRequestException("Value '$value' already exists in '$resource'");
            }

            $currentData[] = $value;

            $this->writeResource($resource, $currentData);

            return count($currentData);
        }

        $id = $this->getNextId($currentData);

        foreach ($currentData as $item) {
            if (is_array($item) === false) {
                continue;
            }

            $diff = array_diff_assoc($data, $item);

            if (empty($diff) === true) {
                throw new HttpBadRequestException("Duplicate entry in '$resource'");
            }
        }

        $data['id'] = $id;

        $currentData[] = $data;

        $this->writeResource($resource, $currentData);

        return $id;
    }

    /**
     * @param string $resource
     * @param array $condition
     * @return int
     * @throws HttpBadRequestException
     */
    public function delete(string $resource, array $condition = []): int
    {
        $rows = $this->readResource($resource);

        $originalCount = count($rows);

        if (empty($condition) === true) {
            throw new httpBadRequestException(
                "Не задано, что удалять из ресурса «{$resource}»"
            );
        }

        $condition = array_map('urldecode', $condition);

        $isSimpleArray = array_values($rows) === $rows;

        if ($isSimpleArray === true) {

            $toDelete = (string) reset($condition);

            if (in_array($toDelete, array_map('strval', $rows), true) === false) {
                throw new HttpNotFoundException(
                    "Значение «{$toDelete}» не найдено в ресурсе «{$resource}»"
                );
            }

            $filtered = array_filter(
                $rows,
                fn($item) => (string)$item !== $toDelete
            );

            $this->writeResource($resource, array_values($filtered));

            return $originalCount - count($filtered);

        }

        $filtered = array_filter(
            $rows,
            fn($row) => ! $this->matchesAll($row, $condition)
        );

        if (count($filtered) === $originalCount) {
            throw new httpBadRequestException(
                "В ресурсе «{$resource}» не найдено записей, соответствующих условию"
            );
        }

        $this->writeResource($resource, array_values($filtered));

        return $originalCount - count($filtered);
    }

    /**
     * @return string
     */
    public function getLastInsertId(): string
    {
        return (string)($this->lastInsertId ?? '');
    }

    /**
     * @param string $resource
     * @param string $column
     * @param mixed $value
     * @return bool
     */
    public function isExist(string $resource, string $column, mixed $value): bool
    {
        foreach ($this->readResource($resource) as $row) {
            if (isset($row[$column]) === true && (string)$row[$column] === (string)$value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $resource
     * @return array
     */
    private function readResource(string $resource): array
    {
        $path = $this->resolvePath($resource);

        if (file_exists($path) === false) {
            return [];
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException(sprintf('Cannot read file "%s".', $path));
        }

        return json_decode($content, true, 512, self::JSON_OPTIONS);
    }

    /**
     * @throws RuntimeException
     */
    private function writeResource(string $resource, array $rows): void
    {
        $path = $this->resolvePath($resource);
        $json = json_encode($rows, self::JSON_OPTIONS);

        if (file_put_contents($path, $json) === false) {
            throw new RuntimeException(sprintf('Cannot write file "%s".', $path));
        }
    }

    /**
     * @param string $resource
     * @return string
     */
    private function resolvePath(string $resource): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $resource . '.json';
    }

    /**
     * @param array $rows
     * @return int
     */
    private function getNextId(array $rows): int
    {
        $max = 0;

        foreach ($rows as $row) {
            $max = max($max, (int)($row['id'] ?? 0));
        }

        return $max + 1;
    }

    /**
     * @param array $row
     * @param array $conditions
     * @return bool
     */
    private function matchesAll(array $row, array $conditions): bool
    {
        foreach ($conditions as $column => $value) {
            if (array_key_exists($column, $row) === false) {
                return false;
            }

            if (is_array($value) === true && in_array($row[$column], $value, true) === false) {
                return false;
            }

            if (is_array($value) === false && (string)$row[$column] !== (string)$value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $rows
     * @param object $stmt
     * @return array
     */
    private function processQuery(array $rows, object $stmt): array
    {
        $rows = $this->applyWhere($rows, $stmt->whereClause);

        $rows = $this->applyOrderBy($rows, $stmt->orderByClause);

        $rows = $this->applySelect($rows, $stmt->selectFields);

        return $this->applyLimitOffset($rows, $stmt->limit, $stmt->offset);
    }

    /**
     * @param array $rows
     * @param array $clauses
     * @return array
     */
    private function applyWhere(array $rows, array $clauses): array
    {
        if (empty($clauses) === true) {
            return $rows;
        }

        if (is_scalar($rows[0] ?? null) === true) {
            return array_values(array_filter(
                $rows,
                fn($item) => $this->matchesSimpleValue($item, $clauses)
            ));
        }

        return array_values(array_filter(
            $rows,
            fn($row) => $this->matchesMultiple($row, $clauses)
        ));
    }

    /**
     * @param mixed $value
     * @param array $clauses
     * @return bool
     */
    private function matchesSimpleValue(mixed $value, array $clauses): bool
    {
        foreach ($clauses as $clause) {
            foreach ($clause as $col => $val) {
                if ((string)$value !== (string)$val) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param array $row
     * @param array $clauses
     * @return bool
     */
    private function matchesMultiple(array $row, array $clauses): bool
    {
        foreach ($clauses as $cond) {
            if ($this->matchesAll($row, $cond) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $rows
     * @param array $clauses
     * @return array
     */
    private function applyOrderBy(array $rows, array $clauses): array
    {
        if (empty($clauses) === true) {
            return $rows;
        }

        foreach (array_reverse($clauses) as $col) {
            usort($rows, fn($a, $b) => $a[$col] <=> $b[$col]);
        }

        return $rows;
    }

    /**
     * @param array       $rows
     * @param array|null  $fields
     * @return array
     */
    private function applySelect(array $rows, ?array $fields): array
    {
        if (empty($fields) === true || $fields === ['*']) {
            return $rows;
        }

        return array_map(
            fn($row) => is_array($row) === true
                ? array_intersect_key($row, array_flip($fields))
                : $row,
            $rows
        );
    }

    /**
     * @param array $rows
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    private function applyLimitOffset(array $rows, ?int $limit, ?int $offset): array
    {
        return array_slice($rows, $offset ?? 0, $limit);
    }
}

