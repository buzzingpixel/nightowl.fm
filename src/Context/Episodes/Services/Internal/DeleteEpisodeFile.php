<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\Models\EpisodeModel;
use Config\General;
use League\Flysystem\Filesystem;

class DeleteEpisodeFile
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

    public function delete(EpisodeModel $episode): void
    {
        if ($episode->fileLocation === '') {
            return;
        }

        $episodesDir = $this->generalConfig->pathToEpisodesDirectory();

        $episodePath = $episodesDir . '/' . $episode->fileLocation;

        if (! $this->filesystem->has($episodePath)) {
            return;
        }

        $this->filesystem->delete($episodePath);
    }
}
