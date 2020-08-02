<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Services\ClearAllStalledItems;
use App\Context\Queue\Services\DeleteQueuesByIds;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQuery;
use App\Persistence\RecordQueryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function assert;

class ClearAllStalledItemsTest extends TestCase
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

        $deleteQueuesByIds = $this->createMock(
            DeleteQueuesByIds::class
        );

        assert(
            $deleteQueuesByIds instanceof DeleteQueuesByIds,
            $deleteQueuesByIds instanceof MockObject
        );

        $deleteQueuesByIds->expects(self::never())
            ->method(self::anything());

        $service = new ClearAllStalledItems(
            $recordQueryFactory,
            $deleteQueuesByIds
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

        $deleteQueuesByIds = $this->createMock(
            DeleteQueuesByIds::class
        );

        assert(
            $deleteQueuesByIds instanceof DeleteQueuesByIds,
            $deleteQueuesByIds instanceof MockObject
        );

        $deleteQueuesByIds->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo(['record1', 'record2']));

        $service = new ClearAllStalledItems(
            $recordQueryFactory,
            $deleteQueuesByIds
        );

        $service();
    }
}
