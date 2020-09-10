<?php

declare(strict_types=1);

namespace App\Context\Keywords\Services;

use App\Context\Keywords\Models\FetchModel;
use App\Context\Keywords\Models\KeywordModel;
use App\Persistence\Keywords\KeywordRecord;
use App\Persistence\RecordQueryFactory;
use Throwable;

use function array_map;
use function count;

class FetchKeywords
{
    private RecordQueryFactory $recordQueryFactory;

    public function __construct(RecordQueryFactory $recordQueryFactory)
    {
        $this->recordQueryFactory = $recordQueryFactory;
    }

    /**
     * @return KeywordModel[]
     */
    public function fetch(?FetchModel $fetchModel): array
    {
        try {
            return $this->innerFetch($fetchModel);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * @return KeywordModel[]
     */
    private function innerFetch(?FetchModel $fetchModel): array
    {
        $fetchModel ??= new FetchModel();

        $query = $this->recordQueryFactory->make(
            new KeywordRecord()
        )
            ->withOrder('keyword', 'asc')
            ->withLimit($fetchModel->limit)
            ->withOffset($fetchModel->offset);

        if (count($fetchModel->ids) > 0) {
            $query = $query->withWhere(
                'id',
                $fetchModel->ids,
                'IN'
            );
        }

        if (count($fetchModel->keywords) > 0) {
            $query = $query->withWhere(
                'keyword',
                $fetchModel->keywords,
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

        /** @var KeywordRecord[] $records */
        $records = $query->all();

        return array_map(
            static function (KeywordRecord $r): KeywordModel {
                $model = new KeywordModel();

                $model->id = $r->id;

                $model->keyword = $r->keyword;

                return $model;
            },
            $records,
        );
    }
}
