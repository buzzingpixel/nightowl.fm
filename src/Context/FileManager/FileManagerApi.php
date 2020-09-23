<?php

declare(strict_types=1);

namespace App\Context\FileManager;

use App\Context\FileManager\Models\FileCollection;
use App\Context\FileManager\Models\FileModel;
use App\Context\FileManager\Services\FetchAllFiles;
use App\Context\FileManager\Services\GetFileArtworkUrl;
use Psr\Container\ContainerInterface;

use function assert;

class FileManagerApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function fetchAllFiles(): FileCollection
    {
        $service = $this->di->get(FetchAllFiles::class);

        assert($service instanceof FetchAllFiles);

        return $service->fetch();
    }

    /**
     * @param mixed[] $opt
     */
    public function getFileArtworkUrl(FileModel $file, array $opt): ?string
    {
        $service = $this->di->get(GetFileArtworkUrl::class);

        assert($service instanceof GetFileArtworkUrl);

        return $service->get($file, $opt);
    }
}
