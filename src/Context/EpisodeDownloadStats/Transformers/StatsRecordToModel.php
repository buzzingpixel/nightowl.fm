<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Transformers;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadStatsModel;
use App\Context\Episodes\Models\EpisodeModel;
use App\Persistence\Constants;
use App\Persistence\EpisodeDownloadStats\EpisodeDownloadStatsRecord;
use Safe\DateTimeImmutable;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class StatsRecordToModel
{
    public function transform(
        EpisodeDownloadStatsRecord $record,
        EpisodeModel $episode
    ): EpisodeDownloadStatsModel {
        $model = new EpisodeDownloadStatsModel();

        $model->id = $record->id;

        $model->episode = $episode;

        $model->totalDownloads = (int) $record->total_downloads;

        $model->downloadsPastThirtyDays = (int) $record->downloads_past_thirty_days;

        $model->downloadsPastSixtyDays = (int) $record->downloads_past_sixty_days;

        $model->downloadsPastYear = (int) $record->downloads_past_year;

        $model->lastUpdatedAt = DateTimeImmutable::createFromFormat(
            Constants::POSTGRES_OUTPUT_FORMAT,
            $record->last_updated_at,
        );

        return $model;
    }
}
