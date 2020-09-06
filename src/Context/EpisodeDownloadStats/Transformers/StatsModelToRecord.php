<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Transformers;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadStatsModel;
use App\Persistence\EpisodeDownloadStats\EpisodeDownloadStatsRecord;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class StatsModelToRecord
{
    public function transform(
        EpisodeDownloadStatsModel $model
    ): EpisodeDownloadStatsRecord {
        $record = new EpisodeDownloadStatsRecord();

        $record->id = $model->id;

        $record->episode_id = $model->episode->id;

        $record->total_downloads = $model->totalDownloads;

        $record->downloads_past_thirty_days = $model->downloadsPastThirtyDays;

        $record->downloads_past_sixty_days = $model->downloadsPastSixtyDays;

        $record->downloads_past_year = $model->downloadsPastYear;

        return $record;
    }
}
