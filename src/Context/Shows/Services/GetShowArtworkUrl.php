<?php

declare(strict_types=1);

namespace App\Context\Shows\Services;

use App\Context\Shows\Models\ShowModel;
use Config\General;
use Gumlet\ImageResize;
use League\Flysystem\Filesystem;

use function pathinfo;

use const IMAGETYPE_JPEG;

class GetShowArtworkUrl
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

    /**
     * @param mixed[] $opt
     */
    public function __invoke(ShowModel $show, array $opt = []): string
    {
        return $this->get($show, $opt);
    }

    /**
     * @param mixed[] $opt
     */
    public function get(ShowModel $show, array $opt = []): string
    {
        $size = (int) ($opt['size'] ?? 1400);

        $pub = $this->generalConfig->publicPath();

        $photosUrl = '/files/show-art';

        $base = $pub . $photosUrl;

        $fileLocation = $base . '/' . $show->artworkFileLocation;

        $pathInfo = pathinfo($fileLocation);

        $showPathInfo = pathinfo($show->artworkFileLocation);

        $sizedUrl = $this->generalConfig->siteUrl() . $photosUrl .
            '/' .
            $showPathInfo['dirname'] .
            '/' .
            $size .
            '/' .
            $showPathInfo['filename'] .
            '.jpg';

        $sizedDir = $pathInfo['dirname'] .
            '/' .
            $size;

        $sizedPath = $sizedDir .
            '/' .
            $pathInfo['filename'] .
            '.jpg';

        if ($this->filesystem->has($sizedPath)) {
            return $sizedUrl;
        }

        // Source file does not exist
        if (! $this->filesystem->has($fileLocation)) {
            return '';
        }

        $this->filesystem->createDir($sizedDir);

        $image = new ImageResize($fileLocation);
        $image->crop($size, $size);
        /**
         * @psalm-suppress InvalidScalarArgument
         * @phpstan-ignore-next-line
         */
        $image->save($sizedPath, IMAGETYPE_JPEG);

        return $sizedUrl;
    }
}
