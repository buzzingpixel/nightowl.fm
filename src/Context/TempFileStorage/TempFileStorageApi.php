<?php

declare(strict_types=1);

namespace App\Context\TempFileStorage;

use App\Context\TempFileStorage\Models\TempFileStorageModel;
use App\Context\TempFileStorage\Services\CleanUploadedFiles;
use App\Context\TempFileStorage\Services\SaveUploadedFile;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UploadedFileInterface;

use function assert;

class TempFileStorageApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function saveUploadedFile(
        UploadedFileInterface $uploadedFile
    ): TempFileStorageModel {
        $service = $this->di->get(SaveUploadedFile::class);

        assert($service instanceof SaveUploadedFile);

        return $service($uploadedFile);
    }

    public function cleanUploadedFiles(): void
    {
        $service = $this->di->get(CleanUploadedFiles::class);

        assert($service instanceof CleanUploadedFiles);

        $service();
    }
}
