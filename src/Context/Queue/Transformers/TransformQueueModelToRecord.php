<?php

declare(strict_types=1);

namespace App\Context\Queue\Transformers;

use App\Context\Queue\Models\QueueModel;
use App\Persistence\Queue\QueueRecord;
use DateTimeInterface;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class TransformQueueModelToRecord
{
    public function __invoke(QueueModel $model, bool $setInitial = false): QueueRecord
    {
        $record = new QueueRecord();

        $record->id = $model->id;

        $record->handle = $model->handle;

        $record->display_name = $model->displayName;

        $record->has_started = $model->hasStarted ? '1' : '0';

        $record->is_running = $model->isRunning ? '1' : '0';

        $record->assume_dead_after = $model->assumeDeadAfter->format(
            DateTimeInterface::ATOM
        );

        $record->initial_assume_dead_after = $model->initialAssumeDeadAfter
            ->format(DateTimeInterface::ATOM);

        $record->is_finished = $model->isFinished ? '1' : '0';

        $record->finished_due_to_error = $model->finishedDueToError ? '1' : '0';

        $record->error_message = $model->errorMessage;

        $record->percent_complete = $model->percentComplete;

        $record->added_at = $model->addedAt->format(
            DateTimeInterface::ATOM
        );

        if ($model->finishedAt !== null) {
            $record->finished_at = $model->finishedAt->format(
                DateTimeInterface::ATOM
            );
        }

        return $record;
    }
}
