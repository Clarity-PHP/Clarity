<?php

declare(strict_types=1);

namespace framework\clarity\Http\enum;

enum ResourceActionTypesEnum: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case PATCH = 'patch';
}
