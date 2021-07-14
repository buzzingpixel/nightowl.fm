<?php

declare(strict_types=1);

namespace App\Context\Shows\Services;

use App\Context\Shows\Models\ShowModel;
use Config\General;
use League\Flysystem\Filesystem;
use LogicException;

use function array_walk;
use function ltrim;
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
                'New show art does not exist'
            );
        }

        $publicDir = $this->generalConfig->publicPath();

        $targetPath = $publicDir . '/files/show-art/' . $show->id;

        $targetFileName = $show->slug . '.' . $ext;

        $targetFullPath = $targetPath . '/' . $targetFileName;

        $dirContents = $this->filesystem->listContents($targetPath);

        if ($this->filesystem->has($targetPath)) {
            $this->filesystem->deleteDir($targetPath);
        }

        $this->filesystem->createDir($targetPath);

        $this->filesystem->copy(
            $newFileLocation,
            $targetFullPath,
        );

        array_walk(
            $dirContents,
            function (array $item): void {
                if ($item['type'] !== 'dir') {
                    return;
                }

                $absolutePath = '/' . ltrim(
                    $item['path'],
                    '/'
                );

                $this->filesystem->deleteDir($absolutePath);
            },
        );

        $show->artworkFileLocation = $show->id . '/' . $targetFileName;
    }
}
