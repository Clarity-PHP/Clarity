<?php

namespace framework\clarity\Helpers;

use Exception;

class Alias
{
    public static array $aliases = [];

    /**
     * @param string $alias
     * @param string $path
     * @return void
     */
    public static function set(string $alias, string $path): void
    {
        if (str_starts_with($path, '@') === true) {

            $subAlias = substr($path, 1);

            $parentAlias = strtok($subAlias, '/');

            if (isset(framework\clarity\Helpers\Alias::$aliases[$parentAlias]) === true) {

                $parentPath= framework\clarity\Helpers\Alias::$aliases[$parentAlias];

                $path = $parentPath . self::normalizeAlias(str_replace([$parentAlias, '//'], '', ltrim($subAlias, '/')));
            }
        }

        $alias = self::normalizeAlias($alias);

        self::$aliases[$alias] = rtrim($path, '/');
    }

    /**
     * @param string $alias
     * @return bool
     */
    public static function has(string $alias): bool
    {
        return isset(self::$aliases[$alias]) === true;
    }

    /**
     * @param $alias
     * @return string
     * @throws Exception
     */
    public static function get($alias): string
    {
        $alias = self::normalizeAlias($alias);

        if (str_contains($alias, '/') === true) {
            list($parentAlias, $childPath) = explode('/', $alias, 2);

            if (isset(self::$aliases[$parentAlias]) === true) {
                return self::$aliases[$parentAlias] . DIRECTORY_SEPARATOR . $childPath;
            }
        }

        if (isset(self::$aliases[$alias]) === true) {
            return self::$aliases[$alias];
        }

        throw new Exception("Alias '{$alias}' not found.");
    }

    /**
     * @param $alias
     * @return string
     */
    private static function normalizeAlias($alias): string
    {
        return ltrim($alias, '@');
    }
}