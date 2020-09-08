<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\QueueApi;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQueryFactory;

use function assert;

class FetchNextQueueItem
{
    private FetchHelper $fetchHelper;
    private RecordQueryFactory $recordQueryFactory;
    private QueueApi $queueApi;

    public function __construct(
        FetchHelper $fetchHelper,
        RecordQueryFactory $recordQueryFactory,
        QueueApi $queueApi
    ) {
        $this->fetchHelper        = $fetchHelper;
        $this->recordQueryFactory = $recordQueryFactory;
        $this->queueApi           = $queueApi;
    }

    public function __invoke(): ?QueueItemModel
    {
        $record = ($this->recordQueryFactory)(
            new QueueRecord()
        )
            ->withWhere('is_running', '0')
            ->withWhere('is_finished', '0')
            ->withOrder('added_at', 'asc')
            ->one();

        if ($record === null) {
            return null;
        }

        assert($record instanceof QueueRecord);

        $model = $this->fetchHelper->processRecords([$record])[0];

        foreach ($model->items as $item) {
            if ($item->isFinished) {
                continue;
            }

            return $item;
        }

        if (isset($item)) {
            /** @psalm-suppress MixedArgument */
            $this->queueApi->postRun($item);
        }

        return null;
    }
}
