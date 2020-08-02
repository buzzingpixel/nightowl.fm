<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Services\CleanOldItems;
use App\Persistence\Constants;
use App\Persistence\Queue\QueueItemRecord;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQuery;
use App\Persistence\RecordQueryFactory;
use DateTimeZone;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

class CleanOldItemsTest extends TestCase
{
    public function testWhenNoRecords(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $eightDaysAgo = new DateTimeImmutable(
            '8 days ago',
            new DateTimeZone('UTC')
        );

        $recordQuery = $this->createMock(RecordQuery::class);

        $recordQuery->expects(self::at(0))
            ->method('withWhere')
            ->with(
                self::equalTo('is_finished'),
                self::equalTo('1'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(1))
            ->method('withWhere')
            ->with(
                self::equalTo('finished_at'),
                self::equalTo($eightDaysAgo->format(
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

        $service = new CleanOldItems(
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
        $eightDaysAgo = new DateTimeImmutable(
            '8 days ago',
            new DateTimeZone('UTC')
        );

        $recordQuery = $this->createMock(RecordQuery::class);

        $recordQuery->expects(self::at(0))
            ->method('withWhere')
            ->with(
                self::equalTo('is_finished'),
                self::equalTo('1'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(1))
            ->method('withWhere')
            ->with(
                self::equalTo('finished_at'),
                self::equalTo($eightDaysAgo->format(
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

        $itemStatement = $this->createMock(
            PDOStatement::class
        );

        $itemStatement->expects(self::once())
            ->method('execute')
            ->with(['queueId1', 'queueId2'])
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::at(0))
            ->method('prepare')
            ->with(
                self::equalTo(
                    'DELETE FROM ' . (new QueueRecord())->getTableName() .
                    ' WHERE id IN (?,?)',
                ),
            )
            ->willReturn($queueStatement);

        $pdo->expects(self::at(1))
            ->method('prepare')
            ->with(
                self::equalTo(
                    'DELETE FROM ' .
                    (new QueueItemRecord())->getTableName() .
                    ' WHERE queue_id IN (?,?)',
                ),
            )
            ->willReturn($itemStatement);

        $service = new CleanOldItems(
            $recordQueryFactory,
            $pdo
        );

        self::assertSame(2, $service());
    }
}
