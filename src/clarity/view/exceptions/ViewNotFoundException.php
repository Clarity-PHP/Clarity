<?php

namespace framework\clarity\view\exceptions;

class ViewNotFoundException extends framework\clarity\Http\router\exceptions\HttpNotFoundException
{
    public function __construct(string $viewName)
    {
        parent::__construct( "Resource not found: " . $viewName);
    }
}