<?php

namespace framework\clarity\Console;

final class Console
{
    const FG_COLORS = [
        'black' => '0;30',
        'red' => '0;31',
        'green' => '0;32',
        'yellow' => '0;33',
        'blue' => '0;34',
        'purple' => '0;35',
        'cyan' => '0;36',
        'white' => '0;37',
    ];

    const BG_COLORS = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'purple' => '45',
        'cyan' => '46',
        'white' => '37',
    ];

    /**
     * @param string $text
     * @param string|null $fgColor
     * @param string|null $bgColor
     * @return string
     */
    public static function format(string $text, ?string $fgColor = null, ?string $bgColor = null): string
    {
        $codes = [];

        if ($fgColor !== null && isset(self::FG_COLORS[$fgColor]) === true) {
            $codes[] = self::FG_COLORS[$fgColor];
        }

        if ($bgColor !== null && isset(self::BG_COLORS[$bgColor]) === true) {
            $codes[] = self::BG_COLORS[$bgColor];
        }

        $ansiCodes = implode(';', $codes);

        return empty($ansiCodes) === false ? "\033[" . $ansiCodes . "m" . $text . "\033[0m" : $text;
    }

    /**
     * @param string $message
     * @param string|null $fgColor
     * @param string|null $bgColor
     * @return void
     */
    public static function line(string $message, ?string $fgColor = null, ?string $bgColor = null): void
    {
        echo self::format($message, $fgColor, $bgColor) . PHP_EOL;
    }

    /**
     * @param array $headers
     * @param array $rows
     * @return void
     */
    public static function table(array $headers, array $rows): void
    {
        $columnWidths = [];

        foreach ($headers as $key => $header) {
            $columnValues = array_column($rows, $key) ?: [];
            $maxValueLength = array_reduce($columnValues, function ($max, $value) {
                return max($max, strlen((string)$value));
            }, 0);

            $columnWidths[$key] = max($maxValueLength, strlen($header)) + 2;
        }

        // Формируем строку заголовков
        $headerLine = '';
        foreach ($headers as $key => $header) {
            $headerLine .= str_pad($header, $columnWidths[$key]);
        }

        self::line($headerLine);

        foreach ($rows as $row) {
            $line = '';

            foreach ($headers as $key => $header) {
                $line .= str_pad($row[$key] ?? '', $columnWidths[$key]);
            }

            self::line($line);
        }
    }
}