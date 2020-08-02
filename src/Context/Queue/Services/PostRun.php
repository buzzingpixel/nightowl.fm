<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\Transformers\TransformQueueItemtoRecord;
use App\Context\Queue\Transformers\TransformQueueModelToRecord;
use App\Persistence\SaveExistingRecord;
use DateTimeZone;
use Safe\DateTimeImmutable;

use function count;

class PostRun
{
    private TransformQueueModelToRecord $queueModelToRecord;
    private TransformQueueItemtoRecord $queueItemToRecord;
    private SaveExistingRecord $saveExistingRecord;

    public function __construct(
        TransformQueueModelToRecord $queueModelToRecord,
        TransformQueueItemtoRecord $queueItemToRecord,
        SaveExistingRecord $saveExistingRecord
    ) {
        $this->queueModelToRecord = $queueModelToRecord;
        $this->queueItemToRecord  = $queueItemToRecord;
        $this->saveExistingRecord = $saveExistingRecord;
    }

    public function __invoke(QueueItemModel $item): void
    {
        $item->isFinished = true;

        $item->finishedAt = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC'),
        );

        $item->queue->isRunning = false;

        $totalItems = count($item->queue->items);

        $finishedItems = $this->calcFinishedItems($item->queue);

        $item->queue->percentComplete = $finishedItems / $totalItems * 100;

        if ($finishedItems >= $totalItems) {
            $item->queue->percentComplete = 100.0;

            $item->queue->isFinished = true;

            $item->queue->finishedAt = $item->finishedAt;
        }

        $queueRecord = ($this->queueModelToRecord)($item->queue);

        $itemRecord = ($this->queueItemToRecord)($item);

        ($this->saveExistingRecord)($queueRecord);

        ($this->saveExistingRecord)($itemRecord);
    }

    public function calcFinishedItems(QueueModel $queue): int
    {
        $finished = 0;

        foreach ($queue->items as $item) {
            if (! $item->isFinished) {
                continue;
            }

            $finished++;
        }

        return $finished;
    }
}
