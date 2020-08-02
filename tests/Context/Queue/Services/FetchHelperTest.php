<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\Services\FetchHelper;
use App\Context\Queue\Transformers\QueueItemRecordToModel;
use App\Context\Queue\Transformers\QueueRecordToModel;
use App\Persistence\Constants;
use App\Persistence\Queue\QueueItemRecord;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQuery;
use App\Persistence\RecordQueryFactory;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;
use Tests\TestConfig;

use function assert;
use function Safe\json_encode;

class FetchHelperTest extends TestCase
{
    public function testWhenNoRecords(): void
    {
        $recordQueryFactory = $this->createMock(
            RecordQueryFactory::class
        );

        assert(
            $recordQueryFactory instanceof RecordQueryFactory &&
            $recordQueryFactory instanceof MockObject
        );

        $recordQueryFactory->expects(self::never())
            ->method(self::anything());

        $service = new FetchHelper(
            $recordQueryFactory,
            TestConfig::$di->get(QueueRecordToModel::class),
            TestConfig::$di->get(QueueItemRecordToModel::class)
        );

        self::assertSame(
            [],
            $service->processRecords([])
        );
    }

    public function testWhenNoItemRecords(): void
    {
        $record1AssumeDeadAfter = new DateTimeImmutable(
            '10 year ago',
            new DateTimeZone('UTC')
        );

        $record1AddedAt = new DateTimeImmutable(
            '24 year ago',
            new DateTimeZone('UTC')
        );

        $record1FinishedAt = new DateTimeImmutable(
            '4 year ago',
            new DateTimeZone('UTC')
        );

        $record1 = $this->createRecord(
            '1',
            $record1AssumeDeadAfter,
            $record1AddedAt,
            $record1FinishedAt
        );

        $record2AssumeDeadAfter = new DateTimeImmutable(
            '50 year ago',
            new DateTimeZone('UTC')
        );

        $record2AddedAt = new DateTimeImmutable(
            '51 year ago',
            new DateTimeZone('UTC')
        );

        $record2 = $this->createRecord(
            '2',
            $record2AssumeDeadAfter,
            $record2AddedAt
        );

        $service = new FetchHelper(
            $this->createRecordQueryFactory(),
            TestConfig::$di->get(QueueRecordToModel::class),
            TestConfig::$di->get(QueueItemRecordToModel::class)
        );

        $this->validateModels(
            $service->processRecords([$record1, $record2]),
            $record1AssumeDeadAfter,
            $record2AssumeDeadAfter,
            $record1AddedAt,
            $record2AddedAt,
            $record1FinishedAt
        );
    }

    public function test(): void
    {
        $record1AssumeDeadAfter = new DateTimeImmutable(
            '8 year ago',
            new DateTimeZone('UTC')
        );

        $record1AddedAt = new DateTimeImmutable(
            '13 year ago',
            new DateTimeZone('UTC')
        );

        $record1FinishedAt = new DateTimeImmutable(
            '14 year ago',
            new DateTimeZone('UTC')
        );

        $record1 = $this->createRecord(
            '1',
            $record1AssumeDeadAfter,
            $record1AddedAt,
            $record1FinishedAt
        );

        $record2AssumeDeadAfter = new DateTimeImmutable(
            '20 year ago',
            new DateTimeZone('UTC')
        );

        $record2AddedAt = new DateTimeImmutable(
            '22 year ago',
            new DateTimeZone('UTC')
        );

        $record2 = $this->createRecord(
            '2',
            $record2AssumeDeadAfter,
            $record2AddedAt
        );

        $queueItemRecord1FinishedAt = new DateTimeImmutable(
            '2 years ago',
            new DateTimeZone('UTC')
        );

        $queueItemRecord1 = $this->createQueueItemRecord(
            '1',
            $record2->id,
            12,
            true,
            $queueItemRecord1FinishedAt
        );

        $queueItemRecord2FinishedAt = new DateTimeImmutable(
            '5 years ago',
            new DateTimeZone('UTC')
        );

        $queueItemRecord2 = $this->createQueueItemRecord(
            '2',
            $record1->id,
            43,
            false,
            $queueItemRecord2FinishedAt
        );

        $queueItemRecord1FinishedAt = new DateTimeImmutable(
            '2 years ago',
            new DateTimeZone('UTC')
        );

        $queueItemRecord3 = $this->createQueueItemRecord(
            '3',
            $record2->id,
            21,
            false,
            null
        );

        $recordQueryFactory = $this->createRecordQueryFactory([
            $queueItemRecord1,
            $queueItemRecord2,
            $queueItemRecord3,
        ]);

        $service = new FetchHelper(
            $recordQueryFactory,
            TestConfig::$di->get(QueueRecordToModel::class),
            TestConfig::$di->get(QueueItemRecordToModel::class)
        );

        $models = $service->processRecords([$record1, $record2]);

        $this->validateModels(
            $models,
            $record1AssumeDeadAfter,
            $record2AssumeDeadAfter,
            $record1AddedAt,
            $record2AddedAt,
            $record1FinishedAt,
            false
        );

        $itemsFromModel1 = $models[0]->items;
        $itemsFromModel2 = $models[1]->items;

        self::assertCount(1, $itemsFromModel1);
        self::assertCount(2, $itemsFromModel2);

        self::assertSame('item2', $itemsFromModel1[0]->id);
        self::assertSame('item1', $itemsFromModel2[0]->id);
        self::assertSame('item3', $itemsFromModel2[1]->id);

        self::assertSame($models[0], $itemsFromModel1[0]->queue);
        self::assertSame($models[1], $itemsFromModel2[0]->queue);
        self::assertSame($models[1], $itemsFromModel2[1]->queue);

        self::assertSame(43, $itemsFromModel1[0]->runOrder);
        self::assertSame(12, $itemsFromModel2[0]->runOrder);
        self::assertSame(21, $itemsFromModel2[1]->runOrder);

        self::assertFalse($itemsFromModel1[0]->isFinished);
        self::assertTrue($itemsFromModel2[0]->isFinished);
        self::assertFalse($itemsFromModel2[1]->isFinished);

        self::assertSame(
            $queueItemRecord2FinishedAt->format(DateTimeInterface::ATOM),
            $itemsFromModel1[0]->finishedAt->format(DateTimeInterface::ATOM),
        );
        self::assertSame(
            $queueItemRecord1FinishedAt->format(DateTimeInterface::ATOM),
            $itemsFromModel2[0]->finishedAt->format(DateTimeInterface::ATOM),
        );
        self::assertNull($itemsFromModel2[1]->finishedAt);

        self::assertSame('class2', $itemsFromModel1[0]->class);
        self::assertSame('class1', $itemsFromModel2[0]->class);
        self::assertSame('class3', $itemsFromModel2[1]->class);

        self::assertSame('method2', $itemsFromModel1[0]->method);
        self::assertSame('method1', $itemsFromModel2[0]->method);
        self::assertSame('method3', $itemsFromModel2[1]->method);

        self::assertNull($itemsFromModel1[0]->context);
        self::assertSame(['context1'], $itemsFromModel2[0]->context);
        self::assertNull($itemsFromModel2[1]->context);
    }

    private function createRecord(
        string $number,
        DateTimeImmutable $assumeDeadAfter,
        DateTimeImmutable $recordAddedAt,
        ?DateTimeImmutable $recordFinishedAt = null
    ): QueueRecord {
        $record = new QueueRecord();

        $record->id = 'recordId' . $number;

        $record->handle = 'recordHandle' . $number;

        $record->display_name = 'displayName' . $number;

        $record->has_started = $number === '1';

        $record->assume_dead_after = $assumeDeadAfter->format(
            Constants::POSTGRES_OUTPUT_FORMAT
        );

        $record->initial_assume_dead_after = $assumeDeadAfter->format(
            Constants::POSTGRES_OUTPUT_FORMAT
        );

        $record->is_finished = $number === '1';

        $record->finished_due_to_error = $number === '1';

        $record->error_message = 'errorMessage' . $number;

        if ($number === '1') {
            $record->percent_complete = '12.5';
        }

        $record->added_at = $recordAddedAt->format(
            Constants::POSTGRES_OUTPUT_FORMAT
        );

        if ($recordFinishedAt !== null) {
            $record->finished_at = $recordFinishedAt->format(
                Constants::POSTGRES_OUTPUT_FORMAT
            );
        }

        return $record;
    }

    /**
     * @param QueueItemRecord[] $returnItems
     *
     * @return RecordQueryFactory&MockObject
     */
    private function createRecordQueryFactory(
        array $returnItems = []
    ): RecordQueryFactory {
        $recordQueryFactory = $this->createMock(
            RecordQueryFactory::class
        );

        assert(
            $recordQueryFactory instanceof RecordQueryFactory &&
            $recordQueryFactory instanceof MockObject
        );

        $recordQueryFactory->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo(new QueueItemRecord()))
            ->willReturn($this->createRecordQuery($returnItems));

        return $recordQueryFactory;
    }

    /**
     * @param QueueItemRecord[] $returnItems
     *
     * @return RecordQuery&MockObject
     */
    private function createRecordQuery(array $returnItems = []): RecordQuery
    {
        $recordQuery = $this->createMock(
            RecordQuery::class
        );

        assert(
            $recordQuery instanceof RecordQuery &&
            $recordQuery instanceof MockObject
        );

        $recordQuery->expects(self::once())
            ->method('withWhere')
            ->with(
                self::equalTo('queue_id'),
                self::equalTo(['recordId1', 'recordId2']),
                self::equalTo('IN'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::once())
            ->method('withOrder')
            ->with(
                self::equalTo('run_order'),
                self::equalTo('asc'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::once())
            ->method('all')
            ->willReturn($returnItems);

        return $recordQuery;
    }

    /**
     * @param QueueModel[] $models
     */
    private function validateModels(
        array $models,
        DateTimeImmutable $record1AssumeDeadAfter,
        DateTimeImmutable $record2AssumeDeadAfter,
        DateTimeImmutable $record1AddedAt,
        DateTimeImmutable $record2AddedAt,
        DateTimeImmutable $record1FinishedAt,
        bool $emptyItems = true
    ): void {
        self::assertCount(2, $models);

        $model1 = $models[0];
        $model2 = $models[1];

        assert($model1 instanceof QueueModel);
        assert($model2 instanceof QueueModel);

        self::assertSame('recordId1', $model1->id);
        self::assertSame('recordId2', $model2->id);

        self::assertSame('recordHandle1', $model1->handle);
        self::assertSame('recordHandle2', $model2->handle);

        self::assertSame('displayName1', $model1->displayName);
        self::assertSame('displayName2', $model2->displayName);

        self::assertTrue($model1->hasStarted);
        self::assertFalse($model2->hasStarted);

        self::assertTrue($model1->isRunning);
        self::assertFalse($model2->isRunning);

        self::assertSame(
            $record1AssumeDeadAfter->format(DateTimeInterface::ATOM),
            $model1->assumeDeadAfter->format(DateTimeInterface::ATOM),
        );
        self::assertSame(
            $record2AssumeDeadAfter->format(DateTimeInterface::ATOM),
            $model2->assumeDeadAfter->format(DateTimeInterface::ATOM),
        );

        self::assertTrue($model1->isFinished);
        self::assertFalse($model2->isFinished);

        self::assertTrue($model1->finishedDueToError);
        self::assertFalse($model2->finishedDueToError);

        self::assertSame('errorMessage1', $model1->errorMessage);
        self::assertSame('errorMessage2', $model2->errorMessage);

        self::assertSame(12.5, $model1->percentComplete);
        self::assertSame(0.0, $model2->percentComplete);

        self::assertSame(
            $record1AddedAt->format(DateTimeInterface::ATOM),
            $model1->addedAt->format(DateTimeInterface::ATOM),
        );
        self::assertSame(
            $record2AddedAt->format(DateTimeInterface::ATOM),
            $model2->addedAt->format(DateTimeInterface::ATOM),
        );

        self::assertSame(
            $record1FinishedAt->format(DateTimeInterface::ATOM),
            $model1->finishedAt->format(DateTimeInterface::ATOM),
        );
        self::assertNull($model2->finishedAt);

        if (! $emptyItems) {
            return;
        }

        self::assertSame([], $model1->items);
        self::assertSame([], $model2->items);
    }

    private function createQueueItemRecord(
        string $number,
        string $queueId,
        int $runOrder,
        bool $isFinished,
        ?DateTimeImmutable $finishedAt
    ): QueueItemRecord {
        $record = new QueueItemRecord();

        $record->id = 'item' . $number;

        $record->queue_id = $queueId;

        $record->run_order = $runOrder;

        $record->is_finished = $isFinished ? '1' : '0';

        if ($finishedAt !== null) {
            $record->finished_at = $finishedAt->format(
                Constants::POSTGRES_OUTPUT_FORMAT
            );
        }

        $record->class = 'class' . $number;

        $record->method = 'method' . $number;

        if ($number === '1') {
            $record->context = json_encode(['context1']);
        }

        return $record;
    }
}
