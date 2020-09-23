<?php

declare(strict_types=1);

namespace App\Context\FileManager\Services;

use App\Context\FileManager\Models\FileCollection;
use App\Context\FileManager\Models\FileModel;
use Config\General;
use DateTimeZone;
use League\Flysystem\Filesystem;
use Safe\DateTimeImmutable;

use function implode;
use function ltrim;

class FetchAllFiles
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

    public function fetch(): FileCollection
    {
        $path = implode('/', [
            $this->generalConfig->publicPath(),
            'files',
            'general',
        ]);

        $dirUrl = implode('/', [
            $this->generalConfig->siteUrl(),
            'files',
            'general',
        ]);

        $files = $this->filesystem->listContents($path);

        $collection = new FileCollection();

        /** @psalm-suppress MixedAssignment */
        foreach ($files as $file) {
            $type = (string) ($file['type'] ?? '');

            if ($type !== 'file') {
                continue;
            }

            $fileModel = new FileModel();

            /** @noinspection PhpUnhandledExceptionInspection */
            $dateUpdated = new DateTimeImmutable(
                'now',
                new DateTimeZone('UTC'),
            );

            /** @noinspection PhpUnhandledExceptionInspection */
            $dateUpdated = $dateUpdated->setTimestamp(
                (int) ($file['timestamp'] ?? 0)
            );

            $fileModel->path = '/' . ltrim(
                (string) ($file['path'] ?? ''),
                '/'
            );

            $fileModel->dateUpdated = $dateUpdated;

            $fileModel->bytes = (int) ($file['size'] ?? '');

            $fileModel->dirName = (string) ($file['dirname'] ?? '');

            $fileModel->baseName = (string) ($file['basename'] ?? '');

            $fileModel->extension = (string) ($file['extension'] ?? '');

            $fileModel->fileName = (string) ($file['filename'] ?? '');

            $fileModel->publicUrl = implode('/', [
                $dirUrl,
                $fileModel->baseName,
            ]);

            $collection->add($fileModel);
        }

        return $collection->sort(
            'dateUpdated',
            FileCollection::SORT_DESC,
        );
    }
}
