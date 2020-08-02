<?php

declare(strict_types=1);

namespace App\Context\Schedule\Transformers;

use App\Context\Schedule\Frequency;
use App\Context\Schedule\Models\ScheduleItemModel;
use App\Persistence\Constants;
use App\Persistence\Schedule\ScheduleTrackingRecord;
use Safe\DateTimeImmutable;
use Throwable;

use function constant;
use function in_array;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class TransformRecordToModel
{
    public function __invoke(ScheduleTrackingRecord $record): ScheduleItemModel
    {
        $lastRunStartAt = null;

        $lastRunEndAt = null;

        if ($record->last_run_start_at !== '') {
            $lastRunStartAt = DateTimeImmutable::createFromFormat(
                Constants::POSTGRES_OUTPUT_FORMAT,
                $record->last_run_start_at
            );
        }

        if ($record->last_run_end_at !== '') {
            $lastRunEndAt = DateTimeImmutable::createFromFormat(
                Constants::POSTGRES_OUTPUT_FORMAT,
                $record->last_run_end_at
            );
        }

        try {
            $runEvery = (string) constant($record->class . '::RUN_EVERY');
        } catch (Throwable $e) {
            $runEvery = Frequency::ALWAYS;
        }

        $model = new ScheduleItemModel();

        $model->id = $record->id;

        $model->class = $record->class;

        $model->checkRunEveryValue($runEvery);

        $model->runEvery = $runEvery;

        $model->isRunning = in_array(
            $record->is_running,
            [
                '1',
                1,
                'true',
                true,
            ],
            true
        );

        $model->lastRunStartAt = $lastRunStartAt;

        $model->lastRunEndAt = $lastRunEndAt;

        return $model;
    }
}
