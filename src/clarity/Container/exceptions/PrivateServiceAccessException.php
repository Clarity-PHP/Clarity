<?php

namespace framework\clarity\Container\exceptions;

use LogicException;

class PrivateServiceAccessException extends LogicException
{
    /**
     * @param string $serviceName
     */
    public function __construct(string $serviceName)
    {
        parent::__construct("Сервис {$serviceName} является приватным и не может быть доступен из контейнера.");
    }
}