<?php

declare(strict_types=1);

namespace framework\clarity\Http;

use framework\clarity\database\interfaces\DataBaseConnectionInterface;
use framework\clarity\Http\interfaces\ResourceWriterInterface;
use InvalidArgumentException;
use RuntimeException;

class ResourceWriter implements ResourceWriterInterface
{
    private string $resourceName;

    public function __construct(
        private readonly DataBaseConnectionInterface $connection
    )
    {}

    /**
     * @param string $name
     * @return $this
     */
    public function setResourceName(string $name): static
    {
        if (empty($name) === true) {
            throw new InvalidArgumentException('Имя ресурса не передано');
        }

        $this->resourceName = $name;
        return $this;
    }

    /**
     * @param array $values
     * @return void
     */
    public function create(array $values): void
    {
        if (empty($this->resourceName) === true) {
            throw new RuntimeException('Не установлено име ресурса');
        }

        $this->connection->insert($this->resourceName, $values);
    }

    /**
     * @param int|string $id
     * @param array $values
     * @return void
     */
    public function update(int|string $id, array $values): void
    {
        if (empty($this->resourceName) === true) {
            throw new RuntimeException('Не установлено име ресурса');
        }

        $this->connection->update(
            $this->resourceName,
            $values,
            ['id' => $id]
        );
    }

    /**
     * @param int|string $id
     * @param array $values
     * @return void
     */
    public function patch(int|string $id, array $values): void
    {
        if (empty($this->resourceName) === true) {
            throw new RuntimeException('Не установлено име ресурса');
        }

        $this->update($id, $values);
    }

    /**
     * @param int|string $id
     * @return void
     */
    public function delete(int|string $id): void
    {
        if (empty($this->resourceName) === true) {
            throw new RuntimeException('Не установлено име ресурса');
        }

        $this->connection->delete(
            $this->resourceName,
            ['id' => $id]
        );
    }

    /**
     * @return string
     */
    public function getLastInsertId(): string
    {
        if (empty($this->resourceName) === true) {
            throw new RuntimeException('Не установлено име ресурса');
        }

        return $this->connection->getLastInsertId();
    }

}