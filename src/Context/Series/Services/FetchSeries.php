<?php

declare(strict_types=1);

namespace App\Context\Series\Services;

use App\Context\Series\Models\FetchModel;
use App\Context\Series\Models\SeriesModel;
use App\Context\Series\Transformers\RecordToModel;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\ShowApi;
use App\Persistence\RecordQueryFactory;
use App\Persistence\Series\SeriesRecord;
use Throwable;

use function array_map;
use function count;
use function in_array;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class FetchSeries
{
    private RecordQueryFactory $recordQueryFactory;
    private ShowApi $showApi;
    private RecordToModel $recordToModel;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        ShowApi $showApi,
        RecordToModel $recordToModel
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->showApi            = $showApi;
        $this->recordToModel      = $recordToModel;
    }

    /**
     * @return SeriesModel[]
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
     * @return SeriesModel[]
     */
    private function innerRun(?FetchModel $fetchModel = null): array
    {
        $fetchModel ??= new FetchModel();

        $query = $this->recordQueryFactory
            ->make(new SeriesRecord())
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

        if (count($fetchModel->showIds) > 0) {
            $query = $query->withWhere(
                'show_id',
                $fetchModel->showIds,
                'IN'
            );
        }

        $showIds = [];

        // Inspection is duuuumb. We're totally using this down in the array map
        /** @noinspection PhpArrayUsedOnlyForWriteInspection */
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

        if (count($fetchModel->slugs) > 0) {
            $query = $query->withWhere(
                'slug',
                $fetchModel->slugs,
                'IN'
            );
        }

        /** @var SeriesRecord[] $records */
        $records = $query->all();

        $showIdsToFetch = [];

        foreach ($records as $record) {
            if (in_array($record->show_id, $showIds)) {
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

        return array_map(
            fn (SeriesRecord $r) => $this->recordToModel
                ->transform(
                    $r,
                    $showsById[$r->show_id],
                ),
            $records,
        );
    }
}
