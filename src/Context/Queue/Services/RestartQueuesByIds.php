<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Persistence\DatabaseTransactionManager;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQueryFactory;
use App\Persistence\SaveExistingRecord;

use function array_walk;
use function count;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class RestartQueuesByIds
{
    private RecordQueryFactory $recordQueryFactory;
    private SaveExistingRecord $saveExistingRecord;
    private DatabaseTransactionManager $transactionManager;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        SaveExistingRecord $saveExistingRecord,
        DatabaseTransactionManager $transactionManager
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->saveExistingRecord = $saveExistingRecord;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param string[] $ids
     */
    public function __invoke(array $ids): void
    {
        if (count($ids) < 1) {
            return;
        }

        $records = ($this->recordQueryFactory)(new QueueRecord())
            ->withWhere('id', $ids, 'IN')
            ->withWhere('finished_due_to_error', '1')
            ->all();

        if (count($records) < 1) {
            return;
        }

        $this->transactionManager->beginTransaction();

        array_walk(
            $records,
            function (QueueRecord $record): void {
                $record->has_started           = '1';
                $record->is_running            = '0';
                $record->is_finished           = '0';
                $record->finished_due_to_error = '0';
                $record->error_message         = '';
                $record->finished_at           = null;

                ($this->saveExistingRecord)($record);
            }
        );

        $this->transactionManager->commit();
    }
}
