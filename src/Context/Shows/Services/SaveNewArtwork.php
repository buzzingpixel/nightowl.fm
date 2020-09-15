<?php

declare(strict_types=1);

namespace App\Context\Shows\Services;

use App\Context\Shows\Models\ShowModel;
use Config\General;
use League\Flysystem\Filesystem;
use LogicException;

use function pathinfo;

class SaveNewArtwork
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

    public function save(ShowModel $show): void
    {
        $tempDir = $this->generalConfig->pathToStorageDirectory() . '/temp';

        $newFileLocation = $tempDir . '/' . $show->newArtworkFileLocation;

        $newFilePathInfo = pathinfo($show->newArtworkFileLocation);

        $ext = $newFilePathInfo['extension'] ?? 'jpg';

        if (! $this->filesystem->has($newFileLocation)) {
            throw new LogicException(
                'New profile photo does not exist'
            );
        }

        $publicDir = $this->generalConfig->publicPath();

        $targetPath = $publicDir . '/files/show-art/' . $show->id;

        $this->filesystem->createDir($targetPath);

        $targetFileName = $show->slug . '.' . $ext;

        $targetFullPath = $targetPath . '/' . $targetFileName;

        if ($this->filesystem->has($targetFullPath)) {
            $this->filesystem->delete($targetFullPath);
        }

        $this->filesystem->copy(
            $newFileLocation,
            $targetFullPath,
        );

        $show->artworkFileLocation = $show->id . '/' . $targetFileName;
    }
}
