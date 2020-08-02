<?php

declare(strict_types=1);

namespace Tests\Persistence;

use App\Payload\Payload;
use App\Persistence\SaveNewRecord;
use App\Persistence\Schedule\ScheduleTrackingRecord;
use Exception;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

use function implode;

class SaveNewRecordTest extends TestCase
{
    public function testWhenIdIsMissing(): void
    {
        $record = new ScheduleTrackingRecord();

        $pdo = $this->createMock(PDO::class);

        $payload = (new SaveNewRecord($pdo))($record);

        self::assertSame(
            Payload::STATUS_NOT_CREATED,
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

        $payload = (new SaveNewRecord($pdo))($record);

        self::assertSame(
            Payload::STATUS_NOT_CREATED,
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

        $payload = (new SaveNewRecord($pdo))($record);

        self::assertSame(
            Payload::STATUS_NOT_CREATED,
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

        $into = implode(', ', $record->getFields());

        $values = implode(', ', $record->getFields(true));

        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::once())
            ->method('execute')
            ->with(self::equalTo($record->getBindValues()))
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'INSERT INTO ' . $record->getTableName() . ' (' . $into . ') VALUES (' . $values . ')'
            ))
            ->willReturn($statement);

        $payload = (new SaveNewRecord($pdo))($record);

        self::assertSame(
            Payload::STATUS_CREATED,
            $payload->getStatus()
        );

        self::assertSame(
            [
                'message' => 'Created record with id TestId',
                'id' => 'TestId',
            ],
            $payload->getResult()
        );
    }
}
