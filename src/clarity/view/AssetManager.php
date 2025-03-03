<?php

declare(strict_types=1);

namespace framework\clarity\view;

use framework\clarity\Helpers\PathHelper;
use framework\clarity\view\interfaces\AssetManagerInterface;
use RuntimeException;

class AssetManager implements AssetManagerInterface
{
    /**
     * @param string $assetsPath
     */
    public function __construct(public string $assetsPath = '/assets') {}

    /**
     * @inheritDoc
     */
    public function getAssetPath(string $asset): string
    {
        $assetPath = PathHelper::joinPaths($this->assetsPath, ltrim($asset, '/'));

        if (file_exists($assetPath) === false) {
            throw new RuntimeException("Ассет не найден: {$assetPath}");
        }

        return '/assets/' . ltrim($asset, '/');
    }
}