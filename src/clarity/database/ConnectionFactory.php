<?php

declare(strict_types=1);

namespace framework\clarity\database;

use framework\clarity\database\interfaces\ConnectionFactoryInterface;
use framework\clarity\database\interfaces\DataBaseConnectionInterface;

class ConnectionFactory implements ConnectionFactoryInterface
{
    public function createConnection(array $config): DataBaseConnectionInterface
    {
        return match ($config['driver']) {
            'sql' => new sql\DataBaseConnection($config),
            'file' => new file\DataBaseConnection($config),
        };
    }
}
