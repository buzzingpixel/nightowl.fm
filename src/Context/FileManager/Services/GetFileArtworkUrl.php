<?php

declare(strict_types=1);

namespace App\Context\FileManager\Services;

use App\Context\FileManager\Models\FileModel;
use Config\General;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use League\Flysystem\Filesystem;
use Throwable;

use function implode;
use function pathinfo;

use const IMAGETYPE_JPEG;

class GetFileArtworkUrl
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
    public function get(FileModel $file, array $opt = []): ?string
    {
        try {
            return $this->innerGet($file, $opt);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @param mixed[] $opt
     *
     * @throws ImageResizeException
     */
    public function innerGet(FileModel $file, array $opt = []): ?string
    {
        $size = (int) ($opt['size'] ?? 0);

        $width = $size > 0 ? $size : 0;
        $width = (int) ($width > 0 ? $width : ($opt['width'] ?? 0));

        $height = $size > 0 ? $size : 0;
        $height = (int) ($height > 0 ? $height : ($opt['height'] ?? 0));

        if ($width === 0 && $height !== 0) {
            $width = $height;
        } elseif ($height === 0 && $width !== 0) {
            $height = $width;
        }

        $mode = (string) ($opt['mode'] ?? 'crop');
        $mode = $mode === 'crop' || $mode === 'resize' ? $mode : 'crop';

        $filesUrl = 'files/general';

        $fileLocation = $file->path;

        $pathInfo = pathinfo($fileLocation);

        $manipulationsPath = implode('', [
            $pathInfo['filename'],
            '-',
            ($pathInfo['extension'] ?? ''),
            '/',
        ]);

        if ($width === 0 && $height === 0) {
            $manipulationsPath .= 'original';
        } else {
            $manipulationsPath .= implode('-', [
                $width,
                $height,
                $mode,
            ]);
        }

        $sizedUrl = implode('/', [
            $this->generalConfig->siteUrl(),
            $filesUrl,
            $manipulationsPath,
            $pathInfo['filename'] . '.jpg',
        ]);

        $sizedDir = implode('/', [
            $pathInfo['dirname'],
            $manipulationsPath,
        ]);

        $sizedPath = implode('/', [
            $sizedDir,
            $pathInfo['filename'] . '.jpg',
        ]);

        if ($this->filesystem->has($sizedPath)) {
            return $sizedUrl;
        }

        // Source file does not exist
        if (! $this->filesystem->has($fileLocation)) {
            return null;
        }

        $image = new ImageResize($fileLocation);

        if ($width > 0 && $height > 0) {
            if ($mode === 'crop') {
                $image->crop($width, $height);
            } else {
                $image->resize(
                    $width,
                    $height,
                );
            }
        }

        $this->filesystem->createDir($sizedDir);

        /**
         * @psalm-suppress InvalidScalarArgument
         * @phpstan-ignore-next-line
         */
        $image->save($sizedPath, IMAGETYPE_JPEG);

        return $sizedUrl;
    }
}
