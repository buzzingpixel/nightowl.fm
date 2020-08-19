<?php

declare(strict_types=1);

namespace App\Context\Shows\Services;

use App\Context\Shows\Models\ShowModel;
use Config\General;
use League\Flysystem\Filesystem;

class DeleteShowArtwork
{
    private General $generalConfig;
    private Filesystem $filesystem;

    public function __construct(
        General $generalConfig,
        Filesystem $filesystem
    ) {
        $this->generalConfig = $generalConfig;
        $this->filesystem    = $filesystem;
    }

    public function delete(ShowModel $show): void
    {
        $publicDir = $this->generalConfig->publicPath();

        $targetPath = $publicDir . '/files/show-art/' . $show->id;

        if (! $this->filesystem->has($targetPath)) {
            return;
        }

        $this->filesystem->deleteDir($targetPath);
    }
}
