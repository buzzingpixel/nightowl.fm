<?php

declare(strict_types=1);

namespace App\Templating\TwigExtensions;

use App\Context\FileManager\FileManagerApi;
use App\Context\FileManager\Models\FileModel;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetFileArtworkUrl extends AbstractExtension
{
    private FileManagerApi $fileManagerApi;

    public function __construct(FileManagerApi $fileManagerApi)
    {
        $this->fileManagerApi = $fileManagerApi;
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [$this->getFunction()];
    }

    private function getFunction(): TwigFunction
    {
        return new TwigFunction(
            'getFileArtworkUrl',
            [$this, 'get']
        );
    }

    /**
     * @param mixed[] $opt
     */
    public function get(FileModel $file, array $opt = []): ?string
    {
        return $this->fileManagerApi->getFileArtworkUrl($file, $opt);
    }
}
