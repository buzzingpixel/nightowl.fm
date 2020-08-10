<?php

declare(strict_types=1);

namespace App\Context\People\Services;

use App\Context\People\Models\PersonModel;
use Config\General;
use League\Flysystem\Filesystem;

class DeleteUserProfilePhoto
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

    public function delete(PersonModel $person): void
    {
        $publicDir = $this->generalConfig->publicPath();

        $targetPath = $publicDir . '/files/profile-photos/' . $person->id;

        if (! $this->filesystem->has($targetPath)) {
            return;
        }

        $this->filesystem->deleteDir($targetPath);
    }
}
