<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Services;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadStatsModel;
use App\Context\EpisodeDownloadStats\Transformers\StatsRecordToModel;
use App\Context\Episodes\Models\EpisodeModel;
use App\Persistence\EpisodeDownloadStats\EpisodeDownloadStatsRecord;
use App\Persistence\RecordQueryFactory;

use function assert;

class FetchStatsForEpisode
{
    private RecordQueryFactory $queryFactory;
    private StatsRecordToModel $recordToModel;

    public function __construct(
        RecordQueryFactory $queryFactory,
        StatsRecordToModel $recordToModel
    ) {
        $this->queryFactory  = $queryFactory;
        $this->recordToModel = $recordToModel;
    }

    public function fetch(EpisodeModel $episode): ?EpisodeDownloadStatsModel
    {
        $record = $this->queryFactory
            ->make(new EpisodeDownloadStatsRecord())
            ->withWhere('episode_id', $episode->id)
            ->one();

        assert(
            $record instanceof EpisodeDownloadStatsRecord ||
            $record === null
        );

        if ($record === null) {
            return null;
        }

        return $this->recordToModel->transform(
            $record,
            $episode
        );
    }
}
