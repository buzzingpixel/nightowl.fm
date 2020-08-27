<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\EpisodeModel;
use Config\General;
use DateTimeZone;
use League\Flysystem\Filesystem;

use function mb_strpos;
use function pathinfo;
use function str_pad;

use const STR_PAD_LEFT;

class RenamePublishedEpisodeFile
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

    public function rename(EpisodeModel $episode): void
    {
        // If the episode isn't published yet, leave it alone
        if (! $episode->isPublished) {
            return;
        }

        $strPos = mb_strpos(
            $episode->getFileName(),
            'episodeId-'
        );

        // If filename does not begin with episodeId-, it's already been renamed
        if ($strPos !== 0) {
            return;
        }

        $episodesDir = $this->generalConfig->pathToEpisodesDirectory();

        $currentFileLocation = $episodesDir . '/' . $episode->fileLocation;

        // If the file isn't in the filesystem, something went bad wrong
        if (! $this->filesystem->has($currentFileLocation)) {
            return;
        }

        if ($episode->episodeType === EpisodeConstants::EPISODE_TYPE_NUMBERED) {
            $targetFileName = $episode->show->slug . '-' . str_pad(
                (string) $episode->number,
                5,
                '0',
                STR_PAD_LEFT
            );
        } else {
            $createdAt = $episode->createdAt->setTimezone(
                new DateTimeZone('UTC'),
            );

            $targetFileName = $episode->show->slug .
                '-insert-' .
                $createdAt->format('Y-m-d-H-i-s');
        }

        $pathInfo = pathinfo($episode->getFileName());

        $ext = $pathInfo['extension'] ?? '';

        $targetPath = $episodesDir . '/' . $episode->show->id;

        $targetFullPath = $targetPath . '/' . $targetFileName . '.' . $ext;

        $this->filesystem->rename(
            $currentFileLocation,
            $targetFullPath
        );

        $episode->fileLocation = $episode->show->id . '/' . $targetFileName;
    }
}
