<?php

declare(strict_types=1);

namespace framework\clarity\view\interfaces;

interface AssetManagerInterface
{
    /**
     * @var string
     */
    public string $assetsPath {
        get;
        set;
    }

    /**
     * @param string $asset
     * @return string
     */
    public function getAssetPath(string $asset): string;
}