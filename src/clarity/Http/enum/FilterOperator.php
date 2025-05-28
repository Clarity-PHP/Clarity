<?php

namespace framework\clarity\Http\enum;

use framework\clarity\database\interfaces\QueryBuilderInterface;
use InvalidArgumentException;

/**
 * Список поддерживаемых операторов фильтрации
 */
enum FilterOperator: string
{
    case EQ = '$eq';
    case IN = '$in';

    /**
     * @param QueryBuilderInterface $qb
     * @param string $column
     * @param mixed $value
     * @return void
     */
    public function apply(QueryBuilderInterface $qb, string $column, mixed $value): void
    {
        match ($this) {
            self::EQ => $qb->where([$column => $value]),
            self::IN => $qb->whereIn($column, $this->validateArray($value)),
        };
    }

    /**
     * @param mixed $value
     * @return array
     */
    private function validateArray(mixed $value): array
    {
        if (is_array($value) === false) {
            throw new InvalidArgumentException("Value for '{$this->value}' must be an array.");
        }

        return $value;
    }
}