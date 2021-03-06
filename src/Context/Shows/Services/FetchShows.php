<?php

declare(strict_types=1);

namespace App\Context\Shows\Services;

use App\Context\Keywords\Models\KeywordModel;
use App\Context\People\Models\FetchModel as FetchPeopleModel;
use App\Context\People\Models\PersonModel;
use App\Context\People\PeopleApi;
use App\Context\PodcastCategories\Models\FetchModel as FetchPodcastCategoriesModel;
use App\Context\PodcastCategories\PodcastCategoriesApi;
use App\Context\Shows\Models\FetchModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\Transformers\RecordToModel;
use App\Persistence\Episodes\EpisodeGuestsRecord;
use App\Persistence\Episodes\EpisodeKeywordsRecord;
use App\Persistence\Episodes\EpisodeRecord;
use App\Persistence\Keywords\KeywordRecord;
use App\Persistence\RecordQuery;
use App\Persistence\RecordQueryFactory;
use App\Persistence\Shows\ShowHostsRecord;
use App\Persistence\Shows\ShowKeywordsRecord;
use App\Persistence\Shows\ShowPodcastCategoriesRecord;
use App\Persistence\Shows\ShowRecord;
use Exception;
use Throwable;

use function array_map;
use function count;
use function Safe\ksort;

use const SORT_NATURAL;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class FetchShows
{
    private RecordQueryFactory $recordQueryFactory;
    private RecordToModel $recordToModel;
    private PeopleApi $peopleApi;
    private PodcastCategoriesApi $podcastCategoriesApi;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        RecordToModel $recordToModel,
        PeopleApi $peopleApi,
        PodcastCategoriesApi $podcastCategoriesApi
    ) {
        $this->recordQueryFactory   = $recordQueryFactory;
        $this->recordToModel        = $recordToModel;
        $this->peopleApi            = $peopleApi;
        $this->podcastCategoriesApi = $podcastCategoriesApi;
    }

    /**
     * @return ShowModel[]
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
     * @return ShowModel[]
     *
     * @throws Exception
     */
    private function innerRun(?FetchModel $fetchModel = null): array
    {
        $fetchModel ??= new FetchModel();

        $query = $this->recordQueryFactory->make(new ShowRecord())
            ->withOrder('title', 'asc')
            ->withOrder('slug', 'asc')
            ->withLimit($fetchModel->limit)
            ->withOffset($fetchModel->offset);

        if (count($fetchModel->ids) > 0) {
            $query = $query->withWhere(
                'id',
                $fetchModel->ids,
                'IN',
            );
        }

        if (count($fetchModel->notIds) > 0) {
            $query = $query->withWhere(
                'id',
                $fetchModel->notIds,
                '!IN',
            );
        }

        if (count($fetchModel->titles) > 0) {
            $query = $query->withWhere(
                'title',
                $fetchModel->titles,
                'IN'
            );
        }

        if (count($fetchModel->slugs) > 0) {
            $query = $query->withWhere(
                'slug',
                $fetchModel->slugs,
                'IN',
            );
        }

        if (count($fetchModel->statuses) > 0) {
            $query = $query->withWhere(
                'status',
                $fetchModel->statuses,
                'IN',
            );
        }

        if (count($fetchModel->notStatuses) > 0) {
            $query = $query->withWhere(
                'status',
                $fetchModel->notStatuses,
                '!IN',
            );
        }

        if ($fetchModel->explicit !== null) {
            $query = $query->withWhere(
                'explicit',
                $fetchModel->explicit ? '1' : '0',
            );
        }

        if (count($fetchModel->keywords) > 0) {
            $query = $this->buildKeywordsQuery(
                $fetchModel,
                $query,
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

        /** @var ShowRecord[] $records */
        $records = $query->all();

        $recordIds = array_map(
            static fn (ShowRecord $r) => $r->id,
            $records,
        );

        /** @var ShowKeywordsRecord[] $keyWordAssociations */
        $keyWordAssociations = [];

        if (count($recordIds) > 0) {
            /** @var ShowKeywordsRecord[] $keyWordAssociations */
            $keyWordAssociations = $this->recordQueryFactory
                ->make(new ShowKeywordsRecord())
                ->withWhere('show_id', $recordIds, 'IN')
                ->all();
        }

        $keywordIds = array_map(
            static fn (ShowKeywordsRecord $r) => $r->keyword_id,
            $keyWordAssociations,
        );

        $keywordRecords = [];

        if (count($keywordIds) > 0) {
            /** @var KeywordRecord[] $keywordRecords */
            $keywordRecords = $this->recordQueryFactory
                ->make(new KeywordRecord())
                ->withWhere('id', $keywordIds, 'IN')
                ->withOrder('keyword', 'asc')
                ->all();
        }

        $keywordRecordsById = [];
        foreach ($keywordRecords as $record) {
            $keywordRecordsById[$record->id] = $record;
        }

        $keyWordRecordsByShowId = [];
        foreach ($keyWordAssociations as $keyWordAssociationRecord) {
            $keywordId = $keyWordAssociationRecord->keyword_id;

            $keyword = $keywordRecordsById[$keywordId] ?? null;

            if ($keyword === null) {
                continue;
            }

            $showId = $keyWordAssociationRecord->show_id;

            $keyWordRecordsByShowId[$showId][$keyword->keyword] = $keyword;

            ksort(
                $keyWordRecordsByShowId[$showId],
                SORT_NATURAL,
            );
        }

        /** @var ShowHostsRecord[] $hostAssociations */
        $hostAssociations = [];

        if (count($recordIds) > 0) {
            /** @var ShowHostsRecord[] $hostAssociations */
            $hostAssociations = $this->recordQueryFactory
                ->make(new ShowHostsRecord())
                ->withWhere('show_id', $recordIds, 'IN')
                ->all();
        }

        $fetchPeopleModel = new FetchPeopleModel();

        $fetchPeopleModel->ids = array_map(
            static fn (ShowHostsRecord $r) => $r->person_id,
            $hostAssociations,
        );

        $hosts = $this->peopleApi->fetchPeople(
            $fetchPeopleModel
        );

        $hostsById = [];
        foreach ($hosts as $host) {
            $hostsById[$host->id] = $host;
        }

        $hostsByShowId = [];
        foreach ($hostAssociations as $hostAssociationRecord) {
            $host = $hostsById[$hostAssociationRecord->person_id] ?? null;

            if ($host === null) {
                continue;
            }

            $key = $host->lastName . '-' . $host->firstName;

            $hostsByShowId[$hostAssociationRecord->show_id][$key] = $host;

            ksort(
                $hostsByShowId[$hostAssociationRecord->show_id],
                SORT_NATURAL,
            );
        }

        /** @var ShowPodcastCategoriesRecord[] $categoryAssociations */
        $categoryAssociations = [];

        if (count($recordIds) > 0) {
            /** @var ShowPodcastCategoriesRecord[] $categoryAssociations */
            $categoryAssociations = $this->recordQueryFactory
                ->make(new ShowPodcastCategoriesRecord())
                ->withWhere('show_id', $recordIds, 'IN')
                ->all();
        }

        $fetchCategoriesModel = new FetchPodcastCategoriesModel();

        $fetchCategoriesModel->ids = array_map(
            static fn (ShowPodcastCategoriesRecord $r) => $r->podcast_category_id,
            $categoryAssociations,
        );

        $categories = $this->podcastCategoriesApi->fetchCategories(
            $fetchCategoriesModel
        );

        $categoriesById = [];
        foreach ($categories as $category) {
            $categoriesById[$category->id] = $category;
        }

        $categoriesByShowId = [];
        foreach ($categoryAssociations as $catRecord) {
            $cat = $categoriesById[$catRecord->podcast_category_id] ?? null;

            if ($cat === null) {
                continue;
            }

            $categoriesByShowId[$catRecord->show_id][$cat->name] = $cat;

            ksort(
                $categoriesByShowId[$catRecord->show_id],
                SORT_NATURAL,
            );
        }

        return array_map(
            fn (ShowRecord $r) => $this->recordToModel
                ->transform(
                    $r,
                    $keyWordRecordsByShowId[$r->id] ?? [],
                    $hostsByShowId[$r->id] ?? [],
                    $categoriesByShowId[$r->id] ?? [],
                ),
            $records
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
                'IN'
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

        /** @var ShowHostsRecord[] $relatedHostRecords */
        $relatedHostRecords = $this->recordQueryFactory
            ->make(new ShowHostsRecord())
            ->withWhere(
                'person_id',
                $relatedHostIds,
                'IN',
            )
            ->all();

        $showIds = array_map(
            static fn (ShowHostsRecord $r) => $r->show_id,
            $relatedHostRecords,
        );

        if (count($showIds) < 1) {
            throw new Exception();
        }

        return $query->withWhere(
            'id',
            $showIds,
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

        if (count($relatedGuestRecords) < 1) {
            throw new Exception();
        }

        $relatedGuestEpisodeIds = array_map(
            static fn (EpisodeGuestsRecord $r) => $r->episode_id,
            $relatedGuestRecords,
        );

        /** @var EpisodeRecord[] $episodeRecords */
        $episodeRecords = $this->recordQueryFactory
            ->make(new EpisodeRecord())
            ->withWhere(
                'id',
                $relatedGuestEpisodeIds,
                'IN',
            )
            ->all();

        if (count($episodeRecords) < 1) {
            throw new Exception();
        }

        $showIds = array_map(
            static fn (EpisodeRecord $r) => $r->show_id,
            $episodeRecords,
        );

        return $query->withWhere(
            'id',
            $showIds,
            'IN',
        );
    }
}
