<?php

namespace framework\clarity\Helpers;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PathHelper
{
    /**
     * @param string ...$paths
     * @return string
     */
    public static function joinPaths(string ...$paths): string
    {
        $cleanedPaths = array_map(function($path) {
            if (str_starts_with($path, '/') === true || str_starts_with($path, '\\') === true) {
                return rtrim($path, '/\\');
            }

            return trim($path, '/\\');
        }, $paths);

        return rtrim(implode(DIRECTORY_SEPARATOR, $cleanedPaths), DIRECTORY_SEPARATOR);
    }

    /**
     * @param string $directory
     * @param string $type
     * @return array
     */
    public static function getFilesFromDirectory(string $directory, string $type = 'php'): array
    {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {

            if ($file->isFile() === true && $file->getExtension() === $type) {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function isAbsolutePath(string $path): bool
    {
        if (DIRECTORY_SEPARATOR === '/' && str_starts_with($path, '/')) {
            return true;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            return (preg_match('/^[a-zA-Z]:\\\\/', $path) === 1) || str_starts_with($path, '\\\\');
        }

        return false;
    }
}