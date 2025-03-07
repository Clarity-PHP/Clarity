<?php

namespace framework\clarity\view\exceptions;

use framework\clarity\Http\router\exceptions\HttpNotFoundException;

class ViewNotFoundException extends HttpNotFoundException
{
    public function __construct(string $viewName)
    {
        parent::__construct( "Resource not found: " . $viewName);
    }
}