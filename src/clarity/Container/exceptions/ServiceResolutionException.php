<?php

namespace framework\clarity\Container\exceptions;

use LogicException;

class ServiceResolutionException extends LogicException
{
    /**
     * @param string $serviceName
     * @param string $parameter
     */
    public function __construct(string $serviceName, string $parameter)
    {
        parent::__construct("Не удалось разрешить зависимость для параметра {$parameter} в классе {$serviceName}.");
    }
}