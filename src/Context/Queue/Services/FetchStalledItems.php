<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Context\Queue\Models\QueueModel;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQueryFactory;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class FetchStalledItems
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
            ->withWhere('finished_due_to_error', '1')
            ->withOrder('added_at', 'asc')
            ->all();

        return $this->fetchHelper->processRecords($records);
    }
}
