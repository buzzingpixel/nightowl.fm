<?php

declare(strict_types=1);

namespace App\Context\People\Services;

use _HumbugBoxc7aa196c4c1e\Symfony\Component\Console\Exception\LogicException;
use App\Context\People\Models\PersonModel;
use Cocur\Slugify\Slugify;
use Config\General;
use League\Flysystem\Filesystem;

use function pathinfo;

class SaveNewProfilePhoto
{
    private General $generalConfig;
    private Filesystem $filesystem;
    private Slugify $slugify;

    public function __construct(
        General $generalConfig,
        Filesystem $filesystem,
        Slugify $slugify
    ) {
        $this->generalConfig = $generalConfig;
        $this->filesystem    = $filesystem;
        $this->slugify       = $slugify;
    }

    public function save(PersonModel $person): void
    {
        $tempDir = $this->generalConfig->pathToStorageDirectory() . '/temp';

        $newFileLocation = $tempDir . '/' . $person->newPhotoFileLocation;

        $newFilePathInfo = pathinfo($person->newPhotoFileLocation);

        $ext = $newFilePathInfo['extension'] ?? 'jpg';

        if (! $this->filesystem->has($newFileLocation)) {
            throw new LogicException(
                'New profile photo does not exist'
            );
        }

        $publicDir = $this->generalConfig->publicPath();

        $targetPath = $publicDir . '/files/profile-photos/' . $person->id;

        $this->filesystem->createDir($targetPath);

        $targetFileName = $this->slugify->slugify(
            $person->getFullName()
        ) . '.' . $ext;

        $targetFullPath = $targetPath . '/' . $targetFileName;

        $this->filesystem->copy(
            $newFileLocation,
            $targetFullPath,
        );

        $person->photoFileLocation = $person->id . '/' . $targetFileName;
    }
}
