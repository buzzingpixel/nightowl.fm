<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\Models\FetchModel;
use App\Context\Keywords\Models\KeywordModel;
use App\Context\People\Models\PersonModel;
use App\Context\Series\Models\SeriesModel;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\ShowApi;
use App\Context\Shows\ShowConstants;
use App\Persistence\Constants;
use App\Persistence\Episodes\EpisodeGuestsRecord;
use App\Persistence\Episodes\EpisodeHostsRecord;
use App\Persistence\Episodes\EpisodeKeywordsRecord;
use App\Persistence\Episodes\EpisodeRecord;
use App\Persistence\Episodes\EpisodeSeriesRecord;
use App\Persistence\RecordQuery;
use App\Persistence\RecordQueryFactory;
use App\Utilities\SystemClock;
use DateTimeZone;
use Exception;
use Throwable;

use function array_map;
use function count;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

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

    /**
     * @throws Throwable
     */
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

        if (count($fetchModel->notShowIds) > 0) {
            $query = $query->withWhere(
                'show_id',
                $fetchModel->notShowIds,
                '!IN'
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

        if (count($fetchModel->notShows) > 0) {
            $notShowIds = [];

            foreach ($fetchModel->notShows as $show) {
                $notShowIds[] = $show->id;
            }

            $query = $query->withWhere(
                'show_id',
                $notShowIds,
                '!IN'
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

        if ($fetchModel->status !== '') {
            $query = $query->withWhere('status', $fetchModel->status);
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

        if ($fetchModel->search !== '') {
            foreach (EpisodeRecord::getSearchableFields() as $field) {
                $query = $query->withSearch(
                    $field,
                    $fetchModel->search
                );
            }
        }

        if (count($fetchModel->keywords) > 0) {
            $query = $this->buildKeywordsQuery(
                $fetchModel,
                $query
            );
        }

        if (count($fetchModel->hosts) > 0) {
            $query = $this->buildHostsQuery(
                $fetchModel,
                $query,
            );
        }

        if (count($fetchModel->guests) > 0) {
            $query = $this->buildGuestsQuery(
                $fetchModel,
                $query,
            );
        }

        if (count($fetchModel->series) > 0) {
            $query = $this->buildSeriesQuery(
                $fetchModel,
                $query,
            );
        }

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

    /**
     * @throws Exception
     */
    private function buildKeywordsQuery(
        FetchModel $fetchModel,
        RecordQuery $query
    ): RecordQuery {
        $relatedKeywordIds = array_map(
            static fn (KeywordModel $k) => $k->id,
            $fetchModel->keywords,
        );

        /** @var EpisodeKeywordsRecord[] $relatedKeywordRecords */
        $relatedKeywordRecords = $this->recordQueryFactory
            ->make(new EpisodeKeywordsRecord())
            ->withWhere(
                'keyword_id',
                $relatedKeywordIds,
                'IN',
            )
            ->all();

        $episodeIds = array_map(
            static fn (EpisodeKeywordsRecord $r) => $r->episode_id,
            $relatedKeywordRecords,
        );

        if (count($episodeIds) < 1) {
            throw new Exception();
        }

        return $query->withWhere(
            'id',
            $episodeIds,
            'IN',
        );
    }

    /**
     * @throws Exception
     */
    private function buildHostsQuery(
        FetchModel $fetchModel,
        RecordQuery $query
    ): RecordQuery {
        $relatedHostIds = array_map(
            static fn (PersonModel $p) => $p->id,
            $fetchModel->hosts,
        );

        /** @var EpisodeHostsRecord[] $relatedHostRecords */
        $relatedHostRecords = $this->recordQueryFactory
            ->make(new EpisodeHostsRecord())
            ->withWhere(
                'person_id',
                $relatedHostIds,
                'IN',
            )
            ->all();

        $episodeIds = array_map(
            static fn (EpisodeHostsRecord $r) => $r->episode_id,
            $relatedHostRecords,
        );

        if (count($episodeIds) < 1) {
            throw new Exception();
        }

        return $query->withWhere(
            'id',
            $episodeIds,
            'IN',
        );
    }

    /**
     * @throws Exception
     */
    private function buildGuestsQuery(
        FetchModel $fetchModel,
        RecordQuery $query
    ): RecordQuery {
        $relatedGuestIds = array_map(
            static fn (PersonModel $p) => $p->id,
            $fetchModel->guests,
        );

        /** @var EpisodeGuestsRecord[] $relatedGuestRecords */
        $relatedGuestRecords = $this->recordQueryFactory
            ->make(new EpisodeGuestsRecord())
            ->withWhere(
                'person_id',
                $relatedGuestIds,
                'IN',
            )
            ->all();

        $episodeIds = array_map(
            static fn (EpisodeGuestsRecord $r) => $r->episode_id,
            $relatedGuestRecords,
        );

        if (count($episodeIds) < 1) {
            throw new Exception();
        }

        return $query->withWhere(
            'id',
            $episodeIds,
            'IN',
        );
    }

    /**
     * @throws Exception
     */
    private function buildSeriesQuery(
        FetchModel $fetchModel,
        RecordQuery $query
    ): RecordQuery {
        $relatedSeriesIds = array_map(
            static fn (SeriesModel $s) => $s->id,
            $fetchModel->series,
        );

        /** @var EpisodeSeriesRecord[] $relatedSeriesRecords */
        $relatedSeriesRecords = $this->recordQueryFactory
            ->make(new EpisodeSeriesRecord())
            ->withWhere(
                'series_id',
                $relatedSeriesIds,
                'IN',
            )
            ->all();

        $episodeIds = array_map(
            static fn (EpisodeSeriesRecord $r) => $r->episode_id,
            $relatedSeriesRecords,
        );

        if (count($episodeIds) < 1) {
            throw new Exception();
        }

        return $query->withWhere(
            'id',
            $episodeIds,
            'IN',
        );
    }
}
