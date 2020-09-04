<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\Models\FetchModel;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\ShowApi;
use App\Context\Shows\ShowConstants;
use App\Persistence\Constants;
use App\Persistence\Episodes\EpisodeRecord;
use App\Persistence\RecordQuery;
use App\Persistence\RecordQueryFactory;
use App\Utilities\SystemClock;
use DateTimeZone;

use function array_map;
use function count;

class BuildFetchQuery
{
    private RecordQueryFactory $recordQueryFactory;
    private SystemClock $systemClock;
    private ShowApi $showApi;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        SystemClock $systemClock,
        ShowApi $showApi
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->systemClock        = $systemClock;
        $this->showApi            = $showApi;
    }

    public function build(FetchModel $fetchModel): RecordQuery
    {
        $query = $this->recordQueryFactory->make(
            new EpisodeRecord()
        );

        if (count($fetchModel->ids) > 0) {
            $query = $query->withWhere(
                'id',
                $fetchModel->ids,
                'IN'
            );
        }

        if (count($fetchModel->showIds) > 0) {
            $query = $query->withWhere(
                'show_id',
                $fetchModel->showIds,
                'IN'
            );
        }

        if (count($fetchModel->shows) > 0) {
            $showIds = [];

            foreach ($fetchModel->shows as $show) {
                $showIds[] = $show->id;
            }

            $query = $query->withWhere(
                'show_id',
                $showIds,
                'IN'
            );
        }

        if (count($fetchModel->titles) > 0) {
            $query = $query->withWhere(
                'title',
                $fetchModel->titles,
                'IN'
            );
        }

        if (count($fetchModel->statuses) > 0) {
            $query = $query->withWhere(
                'status',
                $fetchModel->statuses,
                'IN'
            );
        }

        if (count($fetchModel->episodeTypes) > 0) {
            $query = $query->withWhere(
                'episode_type',
                $fetchModel->episodeTypes,
                'IN'
            );
        }

        if ($fetchModel->isExplicit !== null) {
            $query = $query->withWhere(
                'explicit',
                $fetchModel->isExplicit ? '1' : '0'
            );
        }

        if ($fetchModel->isPublished !== null) {
            $query = $query->withWhere(
                'is_published',
                $fetchModel->isPublished ? '1' : '0'
            );
        }

        if (count($fetchModel->episodeNumbers) > 0) {
            $query = $query->withWhere(
                'number',
                $fetchModel->episodeNumbers,
                'IN'
            );
        }

        if ($fetchModel->pastPublishedAt) {
            $query = $query->withWhere(
                'publish_at',
                'NULL',
                '!='
            );

            /** @noinspection PhpUnhandledExceptionInspection */
            $datetime = $this->systemClock->getCurrentTime()
                ->setTimezone(new DateTimeZone('UTC'));

            /** @noinspection PhpUnhandledExceptionInspection */
            $format = $datetime->format(
                Constants::POSTGRES_OUTPUT_FORMAT
            );

            $query = $query->withWhere(
                'publish_at',
                $format,
                '<'
            );
        }

        $query = $this->checkHiddenShowsStatus(
            $fetchModel,
            $query
        );

        return $query;
    }

    private function checkHiddenShowsStatus(
        FetchModel $fetchModel,
        RecordQuery $query
    ): RecordQuery {
        if ($fetchModel->excludeEpisodesFromHiddenShows === false) {
            return $query;
        }

        $showFetchModel = new ShowFetchModel();

        $showFetchModel->statuses[] = ShowConstants::SHOW_STATUS_HIDDEN;

        $hiddenShows = $this->showApi->fetchShows($showFetchModel);

        if (count($hiddenShows) < 1) {
            return $query;
        }

        return $query->withWhere(
            'show_id',
            array_map(
                static fn (ShowModel $m) => $m->id,
                $hiddenShows,
            ),
            '!IN',
        );
    }
}