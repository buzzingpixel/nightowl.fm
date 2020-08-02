<?php

declare(strict_types=1);

namespace App\Context\Schedule\Transformers;

use App\Context\Schedule\Models\ScheduleItemModel;
use App\Persistence\Schedule\ScheduleTrackingRecord;
use DateTimeInterface;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class TransformModelToRecord
{
    public function __invoke(ScheduleItemModel $model): ScheduleTrackingRecord
    {
        $record = new ScheduleTrackingRecord();

        $record->id = $model->id;

        $record->class = $model->class;

        $record->is_running = $model->isRunning ? '1' : '0';

        $record->last_run_start_at = '';

        $record->last_run_end_at = '';

        $lastRunStartAt = $model->lastRunStartAt;

        if ($lastRunStartAt !== null) {
            $record->last_run_start_at = $lastRunStartAt
                ->format(DateTimeInterface::ATOM);
        }

        $lastRunEndAt = $model->lastRunEndAt;

        if ($lastRunEndAt !== null) {
            $record->last_run_end_at = $lastRunEndAt
                ->format(DateTimeInterface::ATOM);
        }

        return $record;
    }
}
