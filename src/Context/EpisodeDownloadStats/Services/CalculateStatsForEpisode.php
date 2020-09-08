<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Services;

use App\Context\EpisodeDownloadStats\EpisodeDownloadStatsApi;
use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadStatsModel;
use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadTrackerModel;
use App\Context\Episodes\Models\EpisodeModel;
use DateTimeZone;
use Safe\DateTimeImmutable;

class CalculateStatsForEpisode
{
    private EpisodeDownloadStatsApi $episodeDownloadStatsApi;

    public function __construct(
        EpisodeDownloadStatsApi $episodeDownloadStatsApi
    ) {
        $this->episodeDownloadStatsApi = $episodeDownloadStatsApi;
    }

    /** @var EpisodeDownloadTrackerModel[] */
    private array $incomplete = [];

    public function calculate(EpisodeModel $episode): void
    {
        $thirtyDaysAgo = new DateTimeImmutable(
            '- 30 days',
            new DateTimeZone('UTC'),
        );

        $sixtyDaysAgo = new DateTimeImmutable(
            '- 60 days',
            new DateTimeZone('UTC'),
        );

        $oneYearAgo = new DateTimeImmutable(
            '- 1 year',
            new DateTimeZone('UTC'),
        );

        $trackers = $this->episodeDownloadStatsApi->fetchTrackersForEpisode(
            $episode
        );

        $stats = $this->fetchStatsModel($episode);

        $stats->totalDownloads          = 0;
        $stats->downloadsPastThirtyDays = 0;
        $stats->downloadsPastSixtyDays  = 0;
        $stats->downloadsPastYear       = 0;

        $this->incomplete = [];

        foreach ($trackers as $model) {
            if ($model->isFullRange) {
                $timeStamp = $model->downloadedAt->getTimestamp();

                $stats->totalDownloads += 1;

                if ($timeStamp < $oneYearAgo->getTimestamp()) {
                    continue;
                }

                $stats->downloadsPastYear += 1;

                if ($timeStamp < $sixtyDaysAgo->getTimestamp()) {
                    continue;
                }

                $stats->downloadsPastSixtyDays += 1;

                if ($timeStamp < $thirtyDaysAgo->getTimestamp()) {
                    continue;
                }

                $stats->downloadsPastThirtyDays += 1;

                continue;
            }

            $this->incomplete[$model->id] = $model;
        }

        foreach ($this->incomplete as $model) {
            if ($model->rangeStart !== 0) {
                continue;
            }

            unset($this->incomplete[$model->id]);

            $relatedRanges = $this->findRelatedRangesFromBeginningRange(
                $model
            );

            $totalBytes = 0;

            foreach ($relatedRanges as $range) {
                $totalBytes += $range->rangeEnd - $range->rangeStart + 1;
            }

            $fileTotalBytes = (int) $model->episode->fileSizeBytes;

            if ($totalBytes < $fileTotalBytes) {
                continue;
            }

            $timeStamp = $model->downloadedAt->getTimestamp();

            $stats->totalDownloads += 1;

            if ($timeStamp < $oneYearAgo->getTimestamp()) {
                continue;
            }

            $stats->downloadsPastYear += 1;

            if ($timeStamp < $sixtyDaysAgo->getTimestamp()) {
                continue;
            }

            $stats->downloadsPastSixtyDays += 1;

            if ($timeStamp < $thirtyDaysAgo->getTimestamp()) {
                continue;
            }

            $stats->downloadsPastThirtyDays += 1;
        }

        $this->episodeDownloadStatsApi->saveStats($stats);
    }

    private function fetchStatsModel(
        EpisodeModel $episode
    ): EpisodeDownloadStatsModel {
        $model = $this->episodeDownloadStatsApi->fetchStatsForEpisode(
            $episode
        );

        if ($model === null) {
            $model = new EpisodeDownloadStatsModel();

            $model->episode = $episode;
        }

        $model->lastUpdatedAt = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        return $model;
    }

    /**
     * @return EpisodeDownloadTrackerModel[]
     */
    private function findRelatedRangesFromBeginningRange(
        EpisodeDownloadTrackerModel $model
    ): array {
        $rangeItems = [$model];

        $rangeItem = $model;

        while ($rangeItem !== null) {
            $rangeItem = $this->findNextLogicalRange($rangeItem);

            if ($rangeItem === null) {
                continue;
            }

            $rangeItems[] = $rangeItem;
        }

        /** @psalm-suppress MixedReturnTypeCoercion */
        return $rangeItems;
    }

    private function findNextLogicalRange(
        EpisodeDownloadTrackerModel $model
    ): ?EpisodeDownloadTrackerModel {
        /** @noinspection PhpUnhandledExceptionInspection */
        $beginningTolerance = $model->downloadedAt->modify(
            '- 5 minutes'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $endTolerance = $model->downloadedAt->modify(
            '+ 10 minutes'
        );

        $nextRangeStart = $model->rangeEnd + 1;

        foreach ($this->incomplete as $incompleteModel) {
            if (
                $incompleteModel->downloadedAt->getTimestamp() <
                $beginningTolerance->getTimestamp()
            ) {
                continue;
            }

            if (
                $incompleteModel->downloadedAt->getTimestamp() >
                $endTolerance->getTimestamp()
            ) {
                break;
            }

            if ($nextRangeStart === $incompleteModel->rangeStart) {
                unset($this->incomplete[$incompleteModel->id]);

                return $incompleteModel;
            }
        }

        return null;
    }
}
