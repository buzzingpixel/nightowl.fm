<?php

declare(strict_types=1);

namespace Tests\Persistence;

use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use App\Persistence\Schedule\ScheduleTrackingRecord;
use Exception;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

use function implode;

class SaveExistingRecordTest extends TestCase
{
    public function testWhenIdIsMissing(): void
    {
        $record = new ScheduleTrackingRecord();

        $pdo = $this->createMock(PDO::class);

        $payload = (new SaveExistingRecord($pdo))($record);

        self::assertSame(
            Payload::STATUS_NOT_UPDATED,
            $payload->getStatus()
        );

        self::assertSame(
            ['message' => 'A record ID is required'],
            $payload->getResult()
        );
    }

    public function testWhenPdoThrows(): void
    {
        $record = new ScheduleTrackingRecord();

        $record->id = 'TestId';

        $pdo = $this->createMock(PDO::class);

        $pdo->method('prepare')->willThrowException(new Exception());

        $payload = (new SaveExistingRecord($pdo))($record);

        self::assertSame(
            Payload::STATUS_NOT_UPDATED,
            $payload->getStatus()
        );

        self::assertSame(
            ['message' => 'An unknown error occurred'],
            $payload->getResult()
        );
    }

    public function testWhenStatementIsNotSuccessful(): void
    {
        $record = new ScheduleTrackingRecord();

        $record->id = 'TestId';

        $statement = $this->createMock(PDOStatement::class);

        $statement->method('execute')->willReturn(false);

        $pdo = $this->createMock(PDO::class);

        $pdo->method('prepare')->willReturn($statement);

        $payload = (new SaveExistingRecord($pdo))($record);

        self::assertSame(
            Payload::STATUS_NOT_UPDATED,
            $payload->getStatus()
        );

        self::assertSame(
            ['message' => 'An unknown error occurred'],
            $payload->getResult()
        );
    }

    public function test(): void
    {
        $record = new ScheduleTrackingRecord();

        $record->id = 'TestId';

        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::once())
            ->method('execute')
            ->with(self::equalTo($record->getBindValues()))
            ->willReturn(true);

        $setSql = [];

        foreach ($record->getFields() as $field) {
            if ($field === 'id') {
                continue;
            }

            $setSql[] = $field . '=:' . $field;
        }

        $sql = 'UPDATE schedule_tracking SET ' .
            implode(', ', $setSql) .
            ' WHERE id=:id';

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo($sql))
            ->willReturn($statement);

        $payload = (new SaveExistingRecord($pdo))($record);

        self::assertSame(
            Payload::STATUS_UPDATED,
            $payload->getStatus()
        );
    }
}
