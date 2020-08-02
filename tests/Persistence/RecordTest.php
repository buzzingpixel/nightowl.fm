<?php

declare(strict_types=1);

namespace Tests\Persistence;

use App\Persistence\Schedule\ScheduleTrackingRecord;
use PHPUnit\Framework\TestCase;

class RecordTest extends TestCase
{
    public function test(): void
    {
        $record = new ScheduleTrackingRecord();

        $record->class = 'foo';

        $record->is_running = '1';

        $record->last_run_start_at = 'bar';

        $record->last_run_end_at = 'baz';

        $record->id = 'foo-id';

        self::assertSame(
            'schedule_tracking',
            $record::tableName()
        );

        self::assertSame(
            'schedule_tracking',
            $record->getTableName()
        );

        self::assertSame(
            [
                'class',
                'is_running',
                'last_run_start_at',
                'last_run_end_at',
                'id',
            ],
            $record->getFields(),
        );

        self::assertSame(
            [
                ':class',
                ':is_running',
                ':last_run_start_at',
                ':last_run_end_at',
                ':id',
            ],
            $record->getFields(true),
        );

        self::assertSame(
            [
                ':class' => 'foo',
                ':is_running' => '1',
                ':last_run_start_at' => 'bar',
                ':last_run_end_at' => 'baz',
                ':id' => 'foo-id',
            ],
            $record->getBindValues(),
        );
    }
}
