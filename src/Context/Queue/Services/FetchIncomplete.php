<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Context\Queue\Models\QueueModel;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQueryFactory;

class FetchIncomplete
{
    private FetchHelper $fetchHelper;
    private RecordQueryFactory $recordQueryFactory;

    public function __construct(
        FetchHelper $fetchHelper,
        RecordQueryFactory $recordQueryFactory
    ) {
        $this->fetchHelper        = $fetchHelper;
        $this->recordQueryFactory = $recordQueryFactory;
    }

    /**
     * @return QueueModel[]
     */
    public function __invoke(): array
    {
        /** @var QueueRecord[] $records */
        $records = ($this->recordQueryFactory)(
            new QueueRecord()
        )
            ->withWhere('is_finished', '0')
            ->withOrder('added_at', 'asc')
            ->all();

        return $this->fetchHelper->processRecords($records);
    }
}
