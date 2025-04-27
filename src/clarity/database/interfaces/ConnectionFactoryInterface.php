<?php

declare(strict_types=1);

namespace framework\clarity\database\interfaces;

interface ConnectionFactoryInterface
{
    function createConnection(array $config): DataBaseConnectionInterface;
}
