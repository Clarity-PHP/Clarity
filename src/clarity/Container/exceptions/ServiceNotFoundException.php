<?php

namespace framework\clarity\Container\exceptions;

use LogicException;

class ServiceNotFoundException extends LogicException
{
    /**
     * @param string $serviceName
     */
    public function __construct(string $serviceName)
    {
        parent::__construct("Сервис {$serviceName} не найден в контейнере.");
    }
}