<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\EpisodeModel;
use Config\General;
use DateTimeZone;
use getID3;
use League\Flysystem\Filesystem;
use LogicException;

use function pathinfo;
use function str_pad;

use const STR_PAD_LEFT;

class SaveNewFile
{
    private General $generalConfig;
    private Filesystem $filesystem;
    private getID3 $getID3;

    public function __construct(
        General $generalConfig,
        Filesystem $filesystem,
        getID3 $getID3
    ) {
        $this->generalConfig = $generalConfig;
        $this->filesystem    = $filesystem;
        $this->getID3        = $getID3;
    }

    public function save(EpisodeModel $episode): void
    {
        $tempDir = $this->generalConfig->pathToStorageDirectory() . '/temp';

        $newFileLocation = $tempDir . '/' . $episode->newFileLocation;

        $newFilePathInfo = pathinfo($episode->newFileLocation);

        $ext = $newFilePathInfo['extension'] ?? '';

        if (! $this->filesystem->has($newFileLocation)) {
            throw new LogicException(
                'New episode file does not exist'
            );
        }

        $episodesDir = $this->generalConfig->pathToEpisodesDirectory();

        $targetPath = $episodesDir . '/' . $episode->show->id;

        $this->filesystem->createDir($targetPath);

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

        $targetFileName .= '.' . $ext;

        $targetFullPath = $targetPath . '/' . $targetFileName;

        $mp3Info = $this->getID3->analyze($newFileLocation);

        $episode->fileRuntimeSeconds = (float) $mp3Info['playtime_seconds'];

        $episode->fileSizeBytes = (string) $mp3Info['filesize'];

        $episode->fileMimeType = (string) $mp3Info['mime_type'];

        $episode->fileFormat = (string) $mp3Info['fileformat'];

        if ($this->filesystem->has($targetFullPath)) {
            $this->filesystem->delete($targetFullPath);
        }

        $this->filesystem->copy(
            $newFileLocation,
            $targetFullPath,
        );

        $episode->fileLocation = $episode->show->id . '/' . $targetFileName;
    }
}
