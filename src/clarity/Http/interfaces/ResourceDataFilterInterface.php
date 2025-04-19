<?php

namespace framework\clarity\Http\interfaces;

interface ResourceDataFilterInterface
{
    /**
     * @param string $name
     * @return $this
     */
    public function setResourceName(string $name): static;

    /**
     * @param array $fieldNames
     * @return $this
     */
    public function setAccessibleFields(array $fieldNames): static;

    /**
     * @param array $filterNames
     * @return $this
     */
    public function setAccessibleFilters(array $filterNames): static;

    /**
     * Возврат коллекции ресурсов, отфильтрованных в соответствии с условиями
     *
     * @param array $condition
     * Пример:
     * [
     *     "fields" => [
     *         "id",
     *         "order_id",
     *         "name",
     *     ],
     *     "filter" => [
     *         "order_id" => [
     *             "$eq" => 3,
     *         ],
     *     ],
     * ]
     * @return array
     * Пример:
     * [
     *     [
     *         "id" => 1,
     *         "order_id" => 3,
     *         "name" => "Некоторое имя 1"
     *     ],
     *     [
     *         "id" => 2,
     *         "order_id" => 3,
     *         "name" => "Некоторое имя 2"
     *     ],
     * ]
     */
    public function filterAll(array $condition): array;

    /**
     * Возврат ресурса, отфильтрованного в соответствии с условиями
     *
     * @param array $condition
     * Пример:
     * [
     *     "fields" => [
     *         "id",
     *         "name",
     *     ],
     *     "filter" => [
     *         "id" => [
     *             "$eq" => 1,
     *         ],
     *     ],
     * ]
     * @return array
     * Пример:
     * [
     *     "id" => 1,
     *     "name" => "Некоторое имя 1"
     * ],
     */
    public function filterOne(array $condition): array;
}
