<?php

declare(strict_types=1);

namespace framework\clarity\Console\enums;

enum ConsoleEventsEnum: string
{
    case CONSOLE_INPUT_BEFORE_PARSE = 'Консольный ввод перед парсингом';
    case CONSOLE_INPUT_AFTER_PARSE = 'Консольный ввод после парсинга';
}
