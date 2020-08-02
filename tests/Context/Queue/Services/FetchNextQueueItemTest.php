<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\Services\FetchHelper;
use App\Context\Queue\Services\FetchNextQueueItem;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQuery;
use App\Persistence\RecordQueryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function assert;

class FetchNextQueueItemTest extends TestCase
{
    public function testWhenNoRecord(): void
    {
        $recordQuery = $this->createMock(RecordQuery::class);

        assert(
            $recordQuery instanceof RecordQuery &&
            $recordQuery instanceof MockObject
        );

        $recordQuery->expects(self::at(0))
            ->method('withWhere')
            ->with(
                self::equalTo('is_running'),
                self::equalTo('0'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(1))
            ->method('withWhere')
            ->with(
                self::equalTo('is_finished'),
                self::equalTo('0'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(2))
            ->method('withOrder')
            ->with(
                self::equalTo('added_at'),
                self::equalTo('asc'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(3))
            ->method('one')
            ->willReturn(null);

        $recordQueryFactory = $this->createMock(
            RecordQueryFactory::class
        );

        $recordQueryFactory->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo(new QueueRecord()))
            ->willReturn($recordQuery);

        assert(
            $recordQueryFactory instanceof RecordQueryFactory &&
            $recordQueryFactory instanceof MockObject
        );

        $fetchHelper = $this->createMock(FetchHelper::class);

        assert(
            $fetchHelper instanceof FetchHelper &&
            $fetchHelper instanceof MockObject
        );

        $fetchHelper->expects(self::never())
            ->method(self::anything());

        $service = new FetchNextQueueItem(
            $fetchHelper,
            $recordQueryFactory
        );

        self::assertNull($service());
    }

    public function test(): void
    {
        $itemModel1             = new QueueItemModel();
        $itemModel1->isFinished = true;

        $itemModel2             = new QueueItemModel();
        $itemModel2->isFinished = false;

        $itemModel3             = new QueueItemModel();
        $itemModel3->isFinished = false;

        $model = new QueueModel();

        $model->items = [
            $itemModel1,
            $itemModel2,
            $itemModel3,
        ];

        $record = new QueueRecord();

        $recordQuery = $this->createMock(RecordQuery::class);

        assert(
            $recordQuery instanceof RecordQuery &&
            $recordQuery instanceof MockObject
        );

        $recordQuery->expects(self::at(0))
            ->method('withWhere')
            ->with(
                self::equalTo('is_running'),
                self::equalTo('0'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(1))
            ->method('withWhere')
            ->with(
                self::equalTo('is_finished'),
                self::equalTo('0'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(2))
            ->method('withOrder')
            ->with(
                self::equalTo('added_at'),
                self::equalTo('asc'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(3))
            ->method('one')
            ->willReturn($record);

        $recordQueryFactory = $this->createMock(
            RecordQueryFactory::class
        );

        $recordQueryFactory->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo(new QueueRecord()))
            ->willReturn($recordQuery);

        assert(
            $recordQueryFactory instanceof RecordQueryFactory &&
            $recordQueryFactory instanceof MockObject
        );

        $fetchHelper = $this->createMock(FetchHelper::class);

        assert(
            $fetchHelper instanceof FetchHelper &&
            $fetchHelper instanceof MockObject
        );

        $fetchHelper->expects(self::once())
            ->method('processRecords')
            ->with(self::equalTo([$record]))
            ->willReturn([$model]);

        $service = new FetchNextQueueItem(
            $fetchHelper,
            $recordQueryFactory
        );

        self::assertSame($itemModel2, $service());
    }

    public function testWhenAllItemsFinished(): void
    {
        $itemModel1             = new QueueItemModel();
        $itemModel1->isFinished = true;

        $itemModel2             = new QueueItemModel();
        $itemModel2->isFinished = true;

        $itemModel3             = new QueueItemModel();
        $itemModel3->isFinished = true;

        $model = new QueueModel();

        $model->items = [
            $itemModel1,
            $itemModel2,
            $itemModel3,
        ];

        $record = new QueueRecord();

        $recordQuery = $this->createMock(RecordQuery::class);

        assert(
            $recordQuery instanceof RecordQuery &&
            $recordQuery instanceof MockObject
        );

        $recordQuery->expects(self::at(0))
            ->method('withWhere')
            ->with(
                self::equalTo('is_running'),
                self::equalTo('0'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(1))
            ->method('withWhere')
            ->with(
                self::equalTo('is_finished'),
                self::equalTo('0'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(2))
            ->method('withOrder')
            ->with(
                self::equalTo('added_at'),
                self::equalTo('asc'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(3))
            ->method('one')
            ->willReturn($record);

        $recordQueryFactory = $this->createMock(
            RecordQueryFactory::class
        );

        $recordQueryFactory->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo(new QueueRecord()))
            ->willReturn($recordQuery);

        assert(
            $recordQueryFactory instanceof RecordQueryFactory &&
            $recordQueryFactory instanceof MockObject
        );

        $fetchHelper = $this->createMock(FetchHelper::class);

        assert(
            $fetchHelper instanceof FetchHelper &&
            $fetchHelper instanceof MockObject
        );

        $fetchHelper->expects(self::once())
            ->method('processRecords')
            ->with(self::equalTo([$record]))
            ->willReturn([$model]);

        $service = new FetchNextQueueItem(
            $fetchHelper,
            $recordQueryFactory
        );

        self::assertNull($service());
    }
}
