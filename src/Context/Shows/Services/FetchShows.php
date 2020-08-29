<?php

declare(strict_types=1);

namespace App\Context\Shows\Services;

use App\Context\People\Models\FetchModel as FetchPeopleModel;
use App\Context\People\PeopleApi;
use App\Context\Shows\Models\FetchModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\Transformers\RecordToModel;
use App\Persistence\Keywords\KeywordRecord;
use App\Persistence\RecordQueryFactory;
use App\Persistence\Shows\ShowHostsRecord;
use App\Persistence\Shows\ShowKeywordsRecord;
use App\Persistence\Shows\ShowRecord;
use Throwable;

use function array_map;
use function count;
use function dd;
use function Safe\ksort;

use const SORT_NATURAL;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class FetchShows
{
    private RecordQueryFactory $recordQueryFactory;
    private RecordToModel $recordToModel;
    private PeopleApi $peopleApi;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        RecordToModel $recordToModel,
        PeopleApi $peopleApi
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->recordToModel      = $recordToModel;
        $this->peopleApi          = $peopleApi;
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

        if (count($fetchModel->slugs) > 0) {
            $query = $query->withWhere(
                'slug',
                $fetchModel->slugs,
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

        if ($fetchModel->explicit !== null) {
            $query = $query->withWhere(
                'explicit',
                $fetchModel->explicit ? '1' : '0'
            );
        }

        if (count($fetchModel->keywords) > 0) {
            // TODO: Filter by keywords
            dd('TODO: Filter by keywords');
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

        return array_map(
            fn (ShowRecord $r) => $this->recordToModel
                ->transform(
                    $r,
                    $keyWordRecordsByShowId[$r->id] ?? [],
                    $hostsByShowId[$r->id] ?? [],
                ),
            $records
        );
    }
}
