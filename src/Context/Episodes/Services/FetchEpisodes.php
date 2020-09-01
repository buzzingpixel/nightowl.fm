<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel;
use App\Context\Episodes\Transformers\RecordToModel;
use App\Context\Keywords\Models\KeywordModel;
use App\Context\People\Models\FetchModel as PeopleFetchModel;
use App\Context\People\PeopleApi;
use App\Context\Series\Models\FetchModel as SeriesFetchModel;
use App\Context\Series\SeriesApi;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\ShowApi;
use App\Persistence\Constants;
use App\Persistence\Episodes\EpisodeGuestsRecord;
use App\Persistence\Episodes\EpisodeHostsRecord;
use App\Persistence\Episodes\EpisodeKeywordsRecord;
use App\Persistence\Episodes\EpisodeRecord;
use App\Persistence\Episodes\EpisodeSeriesRecord;
use App\Persistence\Keywords\KeywordRecord;
use App\Persistence\RecordQueryFactory;
use App\Utilities\SystemClock;
use DateTimeZone;
use Safe\Exceptions\DatetimeException;
use Throwable;

use function array_map;
use function assert;
use function count;
use function in_array;
use function Safe\ksort;

use const SORT_NATURAL;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class FetchEpisodes
{
    private RecordQueryFactory $recordQueryFactory;
    private RecordToModel $recordToModel;
    private ShowApi $showApi;
    private PeopleApi $peopleApi;
    private SeriesApi $seriesApi;
    private SystemClock $systemClock;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        RecordToModel $recordToModel,
        ShowApi $showApi,
        PeopleApi $peopleApi,
        SeriesApi $seriesApi,
        SystemClock $systemClock
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->recordToModel      = $recordToModel;
        $this->showApi            = $showApi;
        $this->peopleApi          = $peopleApi;
        $this->seriesApi          = $seriesApi;
        $this->systemClock        = $systemClock;
    }

    /**
     * @return EpisodeModel[]
     */
    public function fetch(?FetchModel $fetchModel = null): array
    {
        try {
            return $this->innerRun($fetchModel);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * @return EpisodeModel[]
     *
     * @throws DatetimeException
     */
    private function innerRun(?FetchModel $fetchModel = null): array
    {
        $fetchModel ??= new FetchModel();

        $query = $this->recordQueryFactory
            ->make(new EpisodeRecord())
            ->withLimit($fetchModel->limit)
            ->withOffset($fetchModel->offset);

        if ($fetchModel->orderByPublishedAt) {
            $query = $query->withOrder(
                'published_at',
                'desc'
            );
        } else {
            $query = $query->withOrder('is_published', 'desc')
                ->withOrder('display_order', 'desc')
                ->withOrder('created_at', 'desc');
        }

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

        $showIds = [];

        $showsById = [];

        if (count($fetchModel->shows) > 0) {
            foreach ($fetchModel->shows as $show) {
                $showIds[]            = $show->id;
                $showsById[$show->id] = $show;
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

        /** @var EpisodeRecord[] $records */
        $records = $query->all();

        if (count($records) < 1) {
            return [];
        }

        $showIdsToFetch = [];

        foreach ($records as $record) {
            if (
                in_array(
                    $record->show_id,
                    $showIds,
                    true,
                )
            ) {
                continue;
            }

            $showIdsToFetch[] = $record->show_id;
        }

        if (count($showIdsToFetch) > 0) {
            $showFetchModel      = new ShowFetchModel();
            $showFetchModel->ids = $showIdsToFetch;

            $shows = $this->showApi->fetchShows($showFetchModel);

            foreach ($shows as $show) {
                $showsById[$show->id] = $show;
            }
        }

        $episodeIds = array_map(
            static fn (EpisodeRecord $r) => $r->id,
            $records
        );

        $people = $this->fetchPeople($episodeIds);

        $keywords = $this->fetchKeyWords($episodeIds);

        $series = $this->fetchSeries($episodeIds);

        return array_map(
            function (EpisodeRecord $record) use (
                $showsById,
                $people,
                $keywords,
                $series
            ): EpisodeModel {
                $show = $showsById[$record->show_id] ?? new ShowModel();

                if ($show->id === '') {
                    $show->id = $record->show_id;
                }

                /** @psalm-suppress MixedArgument */
                return $this->recordToModel->transform(
                    $record,
                    $show,
                    $people['hosts'][$record->id] ?? [],
                    $people['guests'][$record->id] ?? [],
                    $keywords[$record->id] ?? [],
                    $series[$record->id] ?? [],
                );
            },
            $records,
        );
    }

    /**
     * @param string[] $episodeIds
     *
     * @return mixed[]
     */
    private function fetchPeople(array $episodeIds): array
    {
        /** @var EpisodeHostsRecord[] $episodeHostsRecords */
        $episodeHostsRecords = $this->recordQueryFactory
            ->make(new EpisodeHostsRecord())
            ->withWhere('episode_id', $episodeIds, 'IN')
            ->all();

        /** @var EpisodeGuestsRecord[] $episodeGuestsRecords */
        $episodeGuestsRecords = $this->recordQueryFactory
            ->make(new EpisodeGuestsRecord())
            ->withWhere('episode_id', $episodeIds, 'IN')
            ->all();

        $peopleIds = [];

        foreach ($episodeHostsRecords as $record) {
            $peopleIds[$record->person_id] = $record->person_id;
        }

        foreach ($episodeGuestsRecords as $record) {
            $peopleIds[$record->person_id] = $record->person_id;
        }

        if (count($peopleIds) < 1) {
            return [];
        }

        $fetchPeopleModel = new PeopleFetchModel();

        $fetchPeopleModel->ids = $peopleIds;

        $people = $this->peopleApi->fetchPeople(
            $fetchPeopleModel
        );

        $peopleById = [];

        foreach ($people as $person) {
            $peopleById[$person->id] = $person;
        }

        $hostsByEpisodeId = [];

        $guestsByEpisodeId = [];

        foreach ($episodeHostsRecords as $record) {
            $person = $peopleById[$record->person_id] ?? null;

            if ($person === null) {
                continue;
            }

            $hostsByEpisodeId[$record->episode_id][] = $person;
        }

        foreach ($episodeGuestsRecords as $record) {
            $person = $peopleById[$record->person_id] ?? null;

            if ($person === null) {
                continue;
            }

            $guestsByEpisodeId[$record->episode_id][] = $person;
        }

        return [
            'hosts' => $hostsByEpisodeId,
            'guests' => $guestsByEpisodeId,
        ];
    }

    /**
     * @param string[] $episodeIds
     *
     * @return mixed[]
     */
    private function fetchKeyWords(array $episodeIds): array
    {
        /** @var EpisodeKeywordsRecord[] $episodeKeywordsRecords */
        $episodeKeywordsRecords = $this->recordQueryFactory
            ->make(new EpisodeKeywordsRecord())
            ->withWhere('episode_id', $episodeIds, 'IN')
            ->all();

        $keywordIds = [];

        foreach ($episodeKeywordsRecords as $record) {
            $keywordIds[$record->keyword_id] = $record->keyword_id;
        }

        if (count($keywordIds) < 1) {
            return [];
        }

        $keywordRecords = $this->recordQueryFactory
            ->make(new KeywordRecord())
            ->withWhere('id', $keywordIds, 'IN')
            ->withOrder('keyword', 'asc')
            ->all();

        $keywordRecordsById = [];
        foreach ($keywordRecords as $record) {
            $keywordRecordsById[$record->id] = $record;
        }

        $keyWordModelsByEpisodeId = [];

        foreach ($episodeKeywordsRecords as $record) {
            $keywordId = $record->keyword_id;

            $keyword = $keywordRecordsById[$keywordId] ?? null;

            if ($keyword === null) {
                continue;
            }

            assert($keyword instanceof KeywordRecord);

            $episodeId = $record->episode_id;

            $keywordModel = new KeywordModel();

            $keywordModel->id = $keywordId;

            $keywordModel->keyword = $keyword->keyword;

            $keyWordModelsByEpisodeId[$episodeId][$keyword->keyword] = $keywordModel;

            ksort(
                $keyWordModelsByEpisodeId[$episodeId],
                SORT_NATURAL,
            );
        }

        return $keyWordModelsByEpisodeId;
    }

    /**
     * @param string[] $episodeIds
     *
     * @return mixed[]
     */
    private function fetchSeries(array $episodeIds): array
    {
        /** @var EpisodeSeriesRecord[] $episodeSeriesRecords */
        $episodeSeriesRecords = $this->recordQueryFactory
            ->make(new EpisodeSeriesRecord())
            ->withWhere('episode_id', $episodeIds, 'IN')
            ->all();

        $seriesIds = [];

        foreach ($episodeSeriesRecords as $record) {
            $seriesIds[$record->series_id] = $record->series_id;
        }

        if (count($seriesIds) < 1) {
            return [];
        }

        $seriesFetchModel = new SeriesFetchModel();

        $seriesFetchModel->ids = $seriesIds;

        $series = $this->seriesApi->fetchSeries($seriesFetchModel);

        $seriesById = [];

        foreach ($series as $seriesModel) {
            $seriesById[$seriesModel->id] = $seriesModel;
        }

        $seriesByEpisodeId = [];

        foreach ($episodeSeriesRecords as $record) {
            $seriesModel = $seriesById[$record->series_id] ?? null;

            if ($seriesModel === null) {
                continue;
            }

            $seriesByEpisodeId[$record->episode_id][] = $seriesModel;
        }

        return $seriesByEpisodeId;
    }
}
