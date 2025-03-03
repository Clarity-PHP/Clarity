<?php

declare(strict_types=1);

namespace framework\clarity\tests\unit\router\helpers;

class DeleteDirectoryForTestingHelper
{
    /**
     * @param string $dir
     * @return void
     */
    public function deleteDirectory(string $dir): void
    {
        if (is_dir($dir) === false) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $filePath = "$dir/$file";
            is_dir($filePath) ? $this->deleteDirectory($filePath) : unlink($filePath);
        }

        rmdir($dir);
    }
}