<?php

declare(strict_types=1);

namespace App\Context\TempFileStorage\Services;

use App\Utilities\SystemClock;
use Config\General;
use League\Flysystem\Filesystem;

use function array_walk;

class CleanUploadedFiles
{
    private Filesystem $filesystem;
    private SystemClock $clock;

    private string $directory;

    public function __construct(
        General $generalConfig,
        Filesystem $filesystem,
        SystemClock $clock
    ) {
        $this->filesystem = $filesystem;
        $this->clock      = $clock;

        $directory = $generalConfig->pathToStorageDirectory();

        $directory .= '/temp';

        $this->directory = $directory;
    }

    public function __invoke(): void
    {
        $directories = $this->filesystem->listContents(
            $this->directory,
            false
        );

        array_walk(
            $directories,
            [$this, 'processDirectory']
        );
    }

    /**
     * @param array<string, string> $dirInfo
     */
    protected function processDirectory(array $dirInfo): void
    {
        $type = $dirInfo['type'] ?? '';

        if ($type !== 'dir') {
            return;
        }

        $nameTimestamp = (int) ($dirInfo['basename'] ?? 0);

        $twoHoursAgoTimeStamp = $this->clock->getCurrentTime()
            ->modify('- 2 hours')
            ->getTimestamp();

        if ($twoHoursAgoTimeStamp < $nameTimestamp) {
            return;
        }

        $this->filesystem->deleteDir(
            $this->directory . '/' . $nameTimestamp,
        );
    }
}
