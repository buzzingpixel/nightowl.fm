<?php

declare(strict_types=1);

namespace App\Context\Schedule\Services;

use App\Context\Schedule\Models\ScheduleItemModel;
use App\Context\Schedule\Payloads\SchedulesPayload;
use App\Context\Schedule\Transformers\TransformRecordToModel;
use App\Persistence\Schedule\ScheduleTrackingRecord;
use Config\Schedule;
use PDO;
use Throwable;

use function array_fill;
use function array_map;
use function count;
use function implode;

class FetchSchedules
{
    private Schedule $schedule;
    private PDO $pdo;
    private TransformRecordToModel $transformRecordToModel;

    public function __construct(
        Schedule $schedule,
        PDO $pdo,
        TransformRecordToModel $transformRecordToModel
    ) {
        $this->schedule               = $schedule;
        $this->pdo                    = $pdo;
        $this->transformRecordToModel = $transformRecordToModel;
    }

    public function __invoke(): SchedulesPayload
    {
        try {
            return $this->fetch();
        } catch (Throwable $e) {
            return new SchedulesPayload();
        }
    }

    /**
     * @throws Throwable
     */
    private function fetch(): SchedulesPayload
    {
        $classes = $this->schedule->getScheduleClasses();

        if (count($classes) < 1) {
            return new SchedulesPayload();
        }

        $records = $this->fetchRecords($classes);

        return new SchedulesPayload([
            'schedules' => array_map(
                function (string $class) use (
                    $records
                ): ScheduleItemModel {
                    return $this->mapScheduleClassToModel(
                        $class,
                        $records
                    );
                },
                $classes
            ),
        ]);
    }

    /**
     * @param string[] $classes
     *
     * @return array<string, ScheduleTrackingRecord>
     */
    private function fetchRecords(array $classes): array
    {
        $in = implode(
            ',',
            array_fill(0, count($classes), '?')
        );

        $statement = $this->pdo->prepare(
            'SELECT * FROM schedule_tracking WHERE class IN (' . $in . ')'
        );

        $statement->execute($classes);

        /** @var ScheduleTrackingRecord[] $items */
        $items = $statement->fetchAll(
            PDO::FETCH_CLASS,
            ScheduleTrackingRecord::class
        );

        $keyedItems = [];

        foreach ($items as $item) {
            $keyedItems[$item->class] = $item;
        }

        return $keyedItems;
    }

    /**
     * @param array<string, ScheduleTrackingRecord> $records
     */
    private function mapScheduleClassToModel(
        string $class,
        array $records
    ): ScheduleItemModel {
        $record = $records[$class] ?? new ScheduleTrackingRecord();

        $record->class = $class;

        return ($this->transformRecordToModel)($record);
    }
}
