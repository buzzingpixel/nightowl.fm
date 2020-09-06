<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Transformers;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadTrackerModel;
use App\Context\Episodes\Models\EpisodeModel;
use App\Persistence\Constants;
use App\Persistence\EpisodeDownloadStats\EpisodeDownloadTrackerRecord;
use Safe\DateTimeImmutable;

use function in_array;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class TrackerRecordToModel
{
    public function transform(
        EpisodeDownloadTrackerRecord $record,
        EpisodeModel $episode
    ): EpisodeDownloadTrackerModel {
        $model = new EpisodeDownloadTrackerModel();

        $model->id = $record->id;

        $model->episode = $episode;

        $model->isFullRange = in_array(
            $record->is_full_range,
            [
                true,
                'true',
                '1',
                1,
            ],
            true,
        );

        $model->rangeStart = (int) $record->range_start;

        $model->rangeEnd = (int) $record->range_end;

        $model->downloadedAt = DateTimeImmutable::createFromFormat(
            Constants::POSTGRES_OUTPUT_FORMAT,
            $record->downloaded_at,
        );

        return $model;
    }
}
