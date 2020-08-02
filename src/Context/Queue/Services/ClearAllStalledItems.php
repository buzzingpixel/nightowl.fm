<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQueryFactory;

use function array_map;
use function count;

class ClearAllStalledItems
{
    private RecordQueryFactory $recordQueryFactory;
    private DeleteQueuesByIds $deleteQueuesByIds;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        DeleteQueuesByIds $deleteQueuesByIds
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->deleteQueuesByIds  = $deleteQueuesByIds;
    }

    public function __invoke(): void
    {
        /** @var QueueRecord[] $records */
        $records = ($this->recordQueryFactory)(new QueueRecord())
            ->withWhere('finished_due_to_error', '1')
            ->all();

        if (count($records) < 1) {
            return;
        }

        ($this->deleteQueuesByIds)(array_map(
            static fn (QueueRecord $record): string => $record->id,
            $records,
        ));
    }
}
