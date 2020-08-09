<?php

declare(strict_types=1);

namespace App\Context\TempFileStorage\Services;

use App\Context\TempFileStorage\Models\TempFileStorageModel;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use App\Utilities\SystemClock;
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
    private SystemClock $clock;

    public function __construct(
        General $generalConfig,
        Filesystem $filesystem,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        Slugify $slugify,
        SystemClock $clock
    ) {
        $this->generalConfig = $generalConfig;
        $this->filesystem    = $filesystem;
        $this->uuidFactory   = $uuidFactory;
        $this->slugify       = $slugify;
        $this->clock         = $clock;
    }

    public function __invoke(
        UploadedFileInterface $uploadedFile
    ): TempFileStorageModel {
        $timeStamp = $this->clock->getCurrentTime()->getTimestamp();

        $uuid = $this->uuidFactory->uuid1()->toString();

        $directory = $this->generalConfig->pathToStorageDirectory();

        $directory .= '/temp/' . $timeStamp . '/' . $uuid;

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
            $timeStamp . '/' . $uuid . '/' . $name,
            $name,
        );
    }
}
