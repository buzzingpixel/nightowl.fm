<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\Transformers\QueueItemRecordToModel;
use App\Context\Queue\Transformers\QueueRecordToModel;
use App\Persistence\Queue\QueueItemRecord;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQueryFactory;

use function array_map;
use function array_walk;
use function assert;
use function count;

// phpcs:disable SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming.SuperfluousPrefix
// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class FetchHelper
{
    protected RecordQueryFactory $recordQueryFactory;
    protected QueueRecordToModel $queueRecordToModel;
    protected QueueItemRecordToModel $queueItemRecordToModel;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        QueueRecordToModel $queueRecordToModel,
        QueueItemRecordToModel $queueItemRecordToModel
    ) {
        $this->recordQueryFactory     = $recordQueryFactory;
        $this->queueRecordToModel     = $queueRecordToModel;
        $this->queueItemRecordToModel = $queueItemRecordToModel;
    }

    /** @var QueueItemRecord[] */
    private array $mappedItemRecords = [];

    /**
     * @param QueueRecord[] $records
     *
     * @return QueueModel[]
     */
    public function processRecords(array $records): array
    {
        $recordIds = array_map(
            static fn (QueueRecord $r) => $r->id,
            $records,
        );

        if (count($records) < 1) {
            return [];
        }

        $itemRecords = ($this->recordQueryFactory)(
            new QueueItemRecord()
        )
            ->withWhere('queue_id', $recordIds, 'IN')
            ->withOrder('run_order', 'asc')
            ->all();

        $this->mappedItemRecords = [];

        foreach ($itemRecords as $record) {
            assert($record instanceof QueueItemRecord);

            /** @psalm-suppress InvalidPropertyAssignmentValue */
            $this->mappedItemRecords[$record->queue_id][] = $record;
        }

        return array_map(
            [$this, 'mapQueueRecordToModel'],
            $records,
        );
    }

    protected function mapQueueRecordToModel(
        QueueRecord $queueRecord
    ): QueueModel {
        $queueModel = ($this->queueRecordToModel)($queueRecord);

        $itemRecords = $this->mappedItemRecords[$queueModel->id] ?? [];

        /** @psalm-suppress PossiblyInvalidArgument */
        array_walk(
            $itemRecords,
            fn (QueueItemRecord $itemRecord) => ($this->queueItemRecordToModel)(
                $itemRecord,
                $queueModel
            ),
        );

        return $queueModel;
    }
}
