<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Services;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadTrackerModel;
use App\Context\EpisodeDownloadStats\Transformers\TrackerRecordToModel;
use App\Context\Episodes\Models\EpisodeModel;
use App\Persistence\EpisodeDownloadStats\EpisodeDownloadTrackerRecord;
use App\Persistence\RecordQueryFactory;

use function array_map;

class FetchTrackersForEpisode
{
    private RecordQueryFactory $queryFactory;
    private TrackerRecordToModel $recordToModel;

    public function __construct(
        RecordQueryFactory $queryFactory,
        TrackerRecordToModel $recordToModel
    ) {
        $this->queryFactory  = $queryFactory;
        $this->recordToModel = $recordToModel;
    }

    /**
     * @return EpisodeDownloadTrackerModel[]
     */
    public function fetch(EpisodeModel $episode): array
    {
        /** @var EpisodeDownloadTrackerRecord[] $records */
        $records = $this->queryFactory
            ->make(new EpisodeDownloadTrackerRecord())
            ->withWhere('episode_id', $episode->id)
            ->withOrder('downloaded_at', 'asc')
            ->all();

        return array_map(
            fn (EpisodeDownloadTrackerRecord $r) => $this
                ->recordToModel->transform($r, $episode),
            $records,
        );
    }
}
