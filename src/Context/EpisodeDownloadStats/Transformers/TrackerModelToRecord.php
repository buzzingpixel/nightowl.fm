<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Transformers;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadTrackerModel;
use App\Persistence\EpisodeDownloadStats\EpisodeDownloadTrackerRecord;
use DateTimeInterface;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class TrackerModelToRecord
{
    public function transform(
        EpisodeDownloadTrackerModel $model
    ): EpisodeDownloadTrackerRecord {
        $record = new EpisodeDownloadTrackerRecord();

        $record->id = $model->id;

        $record->episode_id = $model->episode->id;

        $record->is_full_range = $model->isFullRange ? '1' : '0';

        $record->range_start = $model->rangeStart;

        $record->range_end = $model->rangeEnd;

        $record->downloaded_at = $model->downloadedAt->format(
            DateTimeInterface::ATOM
        );

        return $record;
    }
}
