<?php

declare(strict_types=1);

namespace App\Context\TempFileStorage\Services;

use App\Context\TempFileStorage\Models\TempFileStorageModel;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Cocur\Slugify\Slugify;
use Config\General;
use League\Flysystem\Filesystem;
use Psr\Http\Message\UploadedFileInterface;

use function pathinfo;

class SaveUploadedFile
{
    private General $generalConfig;
    private Filesystem $filesystem;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private Slugify $slugify;

    public function __construct(
        General $generalConfig,
        Filesystem $filesystem,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        Slugify $slugify
    ) {
        $this->generalConfig = $generalConfig;
        $this->filesystem    = $filesystem;
        $this->uuidFactory   = $uuidFactory;
        $this->slugify       = $slugify;
    }

    public function __invoke(
        UploadedFileInterface $uploadedFile
    ): TempFileStorageModel {
        $uuid = $this->uuidFactory->uuid1()->toString();

        $directory = $this->generalConfig->pathToStorageDirectory();

        $directory .= '/temp/' . $uuid;

        $this->filesystem->createDir($directory);

        $pathInfo = pathinfo((string) $uploadedFile->getClientFilename());

        $name = $this->slugify->slugify($pathInfo['filename']);

        if (($pathInfo['extension'] ?? '') !== '') {
            /** @phpstan-ignore-next-line */
            $name .= '.' . (string) ($pathInfo['extension'] ?? '');
        }

        $filePath = $directory . '/' . $name;

        $uploadedFile->moveTo($filePath);

        return new TempFileStorageModel(
            $uuid . '/' . $name,
            $name,
        );
    }
}
