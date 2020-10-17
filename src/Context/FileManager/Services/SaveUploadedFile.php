<?php

declare(strict_types=1);

namespace App\Context\FileManager\Services;

use App\Payload\Payload;
use Cocur\Slugify\Slugify;
use Config\General;
use League\Flysystem\Filesystem;
use Psr\Http\Message\UploadedFileInterface;
use Throwable;

use function pathinfo;

class SaveUploadedFile
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

    public function save(
        UploadedFileInterface $uploadedFile
    ): Payload {
        try {
            return $this->innerRun($uploadedFile);
        } catch (Throwable $e) {
            $msg = 'An unknown error occurred saving the uploaded file';

            return new Payload(
                Payload::STATUS_ERROR,
                ['message' => $msg]
            );
        }
    }

    private function innerRun(UploadedFileInterface $uploadedFile): Payload
    {
        $publicPath = $this->generalConfig->publicPath();

        $filesPath = $publicPath . '/files/general';

        if (! $this->filesystem->has($filesPath)) {
            $this->filesystem->createDir($filesPath);
        }

        $pathInfo = pathinfo((string) $uploadedFile->getClientFilename());

        $name = $this->slugify->slugify($pathInfo['filename']);

        if (($pathInfo['extension'] ?? '') !== '') {
            /** @phpstan-ignore-next-line */
            $name .= '.' . (string) ($pathInfo['extension'] ?? '');
        }

        $filePath = $filesPath . '/' . $name;

        if ($this->filesystem->has($filePath)) {
            $msg = 'File name already exists';

            return new Payload(
                Payload::STATUS_ERROR,
                ['message' => $msg]
            );
        }

        $uploadedFile->moveTo($filePath);

        $msg = 'Uploaded file saved successfully';

        return new Payload(
            Payload::STATUS_SUCCESSFUL,
            ['message' => $msg]
        );
    }
}
