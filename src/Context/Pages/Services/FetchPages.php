<?php

declare(strict_types=1);

namespace App\Context\Pages\Services;

use App\Context\Pages\Models\FetchModel;
use App\Context\Pages\Models\PageModel;
use App\Context\Pages\Transformers\RecordToModel;
use App\Persistence\Pages\PageRecord;
use App\Persistence\RecordQueryFactory;
use Throwable;

use function array_map;
use function count;

class FetchPages
{
    private RecordQueryFactory $recordQueryFactory;
    private RecordToModel $recordToModel;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        RecordToModel $recordToModel
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->recordToModel      = $recordToModel;
    }

    /**
     * @return PageModel[]
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
     * @return PageModel[]
     */
    private function innerRun(?FetchModel $fetchModel = null): array
    {
        $fetchModel ??= new FetchModel();

        $query = $this->recordQueryFactory->make(new PageRecord())
            ->withOrder('uri', 'asc')
            ->withLimit($fetchModel->limit)
            ->withOffset($fetchModel->offset);

        if (count($fetchModel->ids) > 0) {
            $query = $query->withWhere(
                'id',
                $fetchModel->ids,
                'IN',
            );
        }

        if (count($fetchModel->titles) > 0) {
            $query = $query->withWhere(
                'title',
                $fetchModel->titles,
                'IN'
            );
        }

        if (count($fetchModel->uris) > 0) {
            $query = $query->withWhere(
                'uri',
                $fetchModel->uris,
                'IN'
            );
        }

        /** @var PageRecord[] $records */
        $records = $query->all();

        return array_map(
            fn (PageRecord $r) => $this->recordToModel
                ->transform($r),
            $records,
        );
    }
}
