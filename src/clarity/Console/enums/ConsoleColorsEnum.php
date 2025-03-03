<?php

declare(strict_types=1);

namespace framework\clarity\Console\enums;

enum ConsoleColorsEnum: string
{
    case FG_COLOR_RESET = '0';
    case FG_RED = '31';
    case FG_GREEN = '32';
    case FG_YELLOW = '33';
    case FG_BLUE = '34';
    case FG_CYAN = '36';
}
