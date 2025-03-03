<?php

namespace framework\clarity\Container\interfaces;

interface ParameterStorageInterface
{
    /**
     * Устанавливает значение для параметра.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void;

    /**
     * Получает значение параметра.
     *
     * @param string $key
     * @param mixed $default Значение по умолчанию, если параметр не найден.
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Проверяет наличие параметра.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Удаляет параметр.
     *
     * @param string $key
     * @return void
     */
    public function remove(string $key): void;
}