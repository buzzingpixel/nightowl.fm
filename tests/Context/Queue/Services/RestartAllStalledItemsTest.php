<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Services\RestartAllStalledItems;
use App\Context\Queue\Services\RestartQueuesByIds;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQuery;
use App\Persistence\RecordQueryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function assert;

class RestartAllStalledItemsTest extends TestCase
{
    public function testWhenNoRecords(): void
    {
        $recordQuery = $this->createMock(RecordQuery::class);

        assert(
            $recordQuery instanceof RecordQuery,
            $recordQuery instanceof MockObject
        );

        $recordQuery->expects(self::at(0))
            ->method('withWhere')
            ->with()
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(1))
            ->method('all')
            ->willReturn([]);

        $recordQueryFactory = $this->createMock(RecordQueryFactory::class);

        assert(
            $recordQueryFactory instanceof RecordQueryFactory,
            $recordQueryFactory instanceof MockObject
        );

        $recordQueryFactory->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo(new QueueRecord()))
            ->willReturn($recordQuery);

        $restartQueuesByIds = $this->createMock(
            RestartQueuesByIds::class
        );

        assert(
            $restartQueuesByIds instanceof RestartQueuesByIds,
            $restartQueuesByIds instanceof MockObject
        );

        $restartQueuesByIds->expects(self::never())
            ->method(self::anything());

        $service = new RestartAllStalledItems(
            $recordQueryFactory,
            $restartQueuesByIds
        );

        $service();
    }

    public function test(): void
    {
        $record1     = new QueueRecord();
        $record1->id = 'record1';

        $record2     = new QueueRecord();
        $record2->id = 'record2';

        $recordQuery = $this->createMock(RecordQuery::class);

        assert(
            $recordQuery instanceof RecordQuery,
            $recordQuery instanceof MockObject
        );

        $recordQuery->expects(self::at(0))
            ->method('withWhere')
            ->with()
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(1))
            ->method('all')
            ->willReturn([$record1, $record2]);

        $recordQueryFactory = $this->createMock(RecordQueryFactory::class);

        assert(
            $recordQueryFactory instanceof RecordQueryFactory,
            $recordQueryFactory instanceof MockObject
        );

        $recordQueryFactory->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo(new QueueRecord()))
            ->willReturn($recordQuery);

        $restartQueuesByIds = $this->createMock(
            RestartQueuesByIds::class
        );

        assert(
            $restartQueuesByIds instanceof RestartQueuesByIds,
            $restartQueuesByIds instanceof MockObject
        );

        $restartQueuesByIds->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo(['record1', 'record2']));

        $service = new RestartAllStalledItems(
            $recordQueryFactory,
            $restartQueuesByIds
        );

        $service();
    }
}
