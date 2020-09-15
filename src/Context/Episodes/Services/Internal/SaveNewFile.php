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
use Psr\Log\LoggerInterface;

use function pathinfo;
use function str_pad;

use const STR_PAD_LEFT;

class SaveNewFile
{
    private General $generalConfig;
    private Filesystem $filesystem;
    private getID3 $getID3;
    private LoggerInterface $logger;

    public function __construct(
        General $generalConfig,
        Filesystem $filesystem,
        getID3 $getID3,
        LoggerInterface $logger
    ) {
        $this->generalConfig = $generalConfig;
        $this->filesystem    = $filesystem;
        $this->getID3        = $getID3;
        $this->logger        = $logger;
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

        if (! $episode->isPublished) {
            $targetFileName = 'episodeId-' . $episode->id;
        } elseif ($episode->episodeType === EpisodeConstants::EPISODE_TYPE_NUMBERED) {
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

        if (! isset($mp3Info['playtime_seconds'])) {
            $this->logger->error(
                'getID3: $mp3Info[\'playtime_seconds\'] is missing',
                [
                    'episodeId' => $episode->id,
                    'episodeTitle' => $episode->title,
                    'episodeStatus' => $episode->status,
                    'fileLocation' => $newFileLocation,
                    'targetFileName' => $targetFileName,
                ]
            );
        }

        if (! isset($mp3Info['filesize'])) {
            $this->logger->error(
                'getID3: $mp3Info[\'filesize\'] is missing',
                [
                    'episodeId' => $episode->id,
                    'episodeTitle' => $episode->title,
                    'episodeStatus' => $episode->status,
                    'fileLocation' => $newFileLocation,
                    'targetFileName' => $targetFileName,
                ]
            );
        }

        if (! isset($mp3Info['mime_type'])) {
            $this->logger->error(
                'getID3: $mp3Info[\'mime_type\'] is missing',
                [
                    'episodeId' => $episode->id,
                    'episodeTitle' => $episode->title,
                    'episodeStatus' => $episode->status,
                    'fileLocation' => $newFileLocation,
                    'targetFileName' => $targetFileName,
                ]
            );
        }

        if (! isset($mp3Info['fileformat'])) {
            $this->logger->error(
                'getID3: $mp3Info[\'fileformat\'] is missing',
                [
                    'episodeId' => $episode->id,
                    'episodeTitle' => $episode->title,
                    'episodeStatus' => $episode->status,
                    'fileLocation' => $newFileLocation,
                    'targetFileName' => $targetFileName,
                ]
            );
        }

        $episode->fileRuntimeSeconds = (float) ($mp3Info['playtime_seconds'] ?? 0.0);

        $episode->fileSizeBytes = (string) ($mp3Info['filesize'] ?? '0');

        $episode->fileMimeType = (string) ($mp3Info['mime_type'] ?? 'audio/mpeg');

        $episode->fileFormat = (string) ($mp3Info['fileformat'] ?? 'mp3');

        if ($this->filesystem->has($targetFullPath)) {
            $this->filesystem->delete($targetFullPath);
        }

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
