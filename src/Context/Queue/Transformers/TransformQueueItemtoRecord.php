<?php

declare(strict_types=1);

namespace App\Context\Queue\Transformers;

use App\Context\Queue\Models\QueueItemModel;
use App\Persistence\Queue\QueueItemRecord;
use DateTimeInterface;

use function Safe\json_encode;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class TransformQueueItemtoRecord
{
    public function __invoke(QueueItemModel $model): QueueItemRecord
    {
        $record = new QueueItemRecord();

        $record->id = $model->id;

        $record->queue_id = $model->queue->id;

        $record->run_order = $model->runOrder;

        $record->is_finished = $model->isFinished ? '1' : '0';

        if ($model->finishedAt !== null) {
            $record->finished_at = $model->finishedAt->format(
                DateTimeInterface::ATOM
            );
        }

        $record->class = $model->class;

        $record->method = $model->method;

        if ($model->context !== null) {
            $record->context = json_encode($model->context);
        }

        return $record;
    }
}
