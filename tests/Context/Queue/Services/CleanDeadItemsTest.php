<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Services\CleanDeadItems;
use App\Persistence\Constants;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQuery;
use App\Persistence\RecordQueryFactory;
use DateTimeInterface;
use DateTimeZone;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

class CleanDeadItemsTest extends TestCase
{
    public function testWhenNoRecords(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $currentDate = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        $recordQuery = $this->createMock(RecordQuery::class);

        $recordQuery->expects(self::at(0))
            ->method('withWhere')
            ->with(
                self::equalTo('is_running'),
                self::equalTo('1'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(1))
            ->method('withWhere')
            ->with(
                self::equalTo('assume_dead_after'),
                self::equalTo($currentDate->format(
                    Constants::POSTGRES_OUTPUT_FORMAT
                )),
                self::equalTo('<'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(2))
            ->method('all')
            ->willReturn([]);

        $recordQueryFactory = $this->createMock(
            RecordQueryFactory::class
        );

        $recordQueryFactory->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo(new QueueRecord()))
            ->willReturn($recordQuery);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::never())
            ->method(self::anything());

        $service = new CleanDeadItems(
            $recordQueryFactory,
            $pdo
        );

        self::assertSame(0, $service());
    }

    public function test(): void
    {
        $record1     = new QueueRecord();
        $record1->id = 'queueId1';

        $record2     = new QueueRecord();
        $record2->id = 'queueId2';

        /** @noinspection PhpUnhandledExceptionInspection */
        $currentDate = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        $recordQuery = $this->createMock(RecordQuery::class);

        $recordQuery->expects(self::at(0))
            ->method('withWhere')
            ->with(
                self::equalTo('is_running'),
                self::equalTo('1'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(1))
            ->method('withWhere')
            ->with(
                self::equalTo('assume_dead_after'),
                self::equalTo($currentDate->format(
                    Constants::POSTGRES_OUTPUT_FORMAT
                )),
                self::equalTo('<'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(2))
            ->method('all')
            ->willReturn([$record1, $record2]);

        $recordQueryFactory = $this->createMock(
            RecordQueryFactory::class
        );

        $recordQueryFactory->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo(new QueueRecord()))
            ->willReturn($recordQuery);

        $queueStatement = $this->createMock(
            PDOStatement::class
        );

        $queueStatement->expects(self::once())
            ->method('execute')
            ->with(['queueId1', 'queueId2'])
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(
                self::equalTo(
                    'UPDATE ' . (new QueueRecord())->getTableName() .
                    ' SET is_running = false,' .
                    ' is_finished = true,' .
                    ' finished_due_to_error = true,' .
                    ' error_message = \'Assumed dead\',' .
                    ' finished_at = \'' .
                    $currentDate->format(DateTimeInterface::ATOM) .
                    '\'' .
                    ' WHERE id IN (?,?)',
                ),
            )
            ->willReturn($queueStatement);

        $service = new CleanDeadItems(
            $recordQueryFactory,
            $pdo
        );

        self::assertSame(2, $service());
    }
}
