<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\Services\AddToQueue;
use App\Context\Queue\Transformers\TransformQueueItemtoRecord;
use App\Context\Queue\Transformers\TransformQueueModelToRecord;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\Queue\QueueItemRecord;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\SaveNewRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;
use Tests\TestConfig;

use function assert;

class AddToQueueTest extends TestCase
{
    private function createQueueModel(): QueueModel
    {
        $queueModel = new QueueModel();

        $queueModel->handle = 'foo-bar';

        $queueModel->displayName = 'Foo Bar';

        $queueModel->hasStarted = true;

        $queueModel->isRunning = true;

        $queueModel->isFinished = true;

        $queueModel->finishedDueToError = true;

        $queueModel->errorMessage = 'Bar Error Message';

        $queueModel->percentComplete = 5.6;

        $queueModel->finishedAt = new DateTimeImmutable();

        return $queueModel;
    }

    private function createQueueItemModel(): QueueItemModel
    {
        $queueItem = new QueueItemModel();

        $queueItem->runOrder = 5;

        $queueItem->isFinished = true;

        $queueItem->finishedAt = new DateTimeImmutable();

        $queueItem->class = 'foo-class';

        $queueItem->method = 'foo-method';

        $queueItem->context = ['asdf'];

        return $queueItem;
    }

    private function validateQueueRecordAgainstModel(
        QueueRecord $record,
        QueueModel $model
    ): void {
        self::assertNotEmpty($record->id);

        self::assertSame('foo-bar', $record->handle);

        self::assertSame('Foo Bar', $record->display_name);

        self::assertSame('1', $record->has_started);

        self::assertSame('1', $record->is_running);

        self::assertSame('1', $record->is_finished);

        self::assertSame('1', $record->finished_due_to_error);

        self::assertSame('Bar Error Message', $record->error_message);

        self::assertSame(5.6, $record->percent_complete);

        self::assertSame(
            $model->finishedAt->format(DateTimeImmutable::ATOM),
            $record->finished_at
        );
    }

    private function validateQueueItemRecordAgainstModel(
        QueueItemRecord $record,
        QueueItemModel $model,
        QueueModel $queue
    ): void {
        self::assertNotEmpty($record->id);

        self::assertSame($queue->id, $record->queue_id);

        self::assertSame(1, $record->run_order);

        self::assertSame('1', $record->is_finished);

        self::assertSame(
            $model->finishedAt->format(DateTimeImmutable::ATOM),
            $record->finished_at
        );

        self::assertSame('foo-class', $record->class);

        self::assertSame('foo-method', $record->method);

        self::assertSame('["asdf"]', $record->context);
    }

    public function testWhenThrows(): void
    {
        $queueModel = new QueueModel();

        $transactionManager = $this->createMock(
            DatabaseTransactionManager::class
        );

        assert(
            $transactionManager instanceof DatabaseTransactionManager &&
            $transactionManager instanceof MockObject
        );

        $transactionManager->expects(self::at(0))
            ->method('beginTransaction')
            ->willThrowException(new Exception());

        $transactionManager->expects(self::never())
            ->method('commit');

        $transactionManager->expects(self::at(1))
            ->method('rollBack');

        $saveNewRecord = $this->createMock(
            SaveNewRecord::class
        );

        assert(
            $saveNewRecord instanceof SaveNewRecord &&
            $saveNewRecord instanceof MockObject
        );

        $saveNewRecord->expects(self::never())
            ->method(self::anything());

        $service = new AddToQueue(
            $transactionManager,
            TestConfig::$di->get(UuidFactoryWithOrderedTimeCodec::class),
            $saveNewRecord,
            new TransformQueueModelToRecord(),
            new TransformQueueItemtoRecord(),
        );

        $payload = $service($queueModel);

        self::assertSame(Payload::STATUS_ERROR, $payload->getStatus());

        self::assertSame(
            ['message' => 'An unknown error occurred'],
            $payload->getResult(),
        );
    }

    public function testWhenQueueNotCreated(): void
    {
        $queueModel = $this->createQueueModel();
        $queueItem  = $this->createQueueItemModel();
        $queueModel->addItem($queueItem);

        $transactionManager = $this->createMock(
            DatabaseTransactionManager::class
        );

        assert(
            $transactionManager instanceof DatabaseTransactionManager &&
            $transactionManager instanceof MockObject
        );

        $transactionManager->expects(self::at(0))
            ->method('beginTransaction')
            ->willReturn(true);

        $transactionManager->expects(self::never())
            ->method('commit');

        $transactionManager->expects(self::at(1))
            ->method('rollBack');

        $saveNewRecord = $this->createMock(
            SaveNewRecord::class
        );

        assert(
            $saveNewRecord instanceof SaveNewRecord &&
            $saveNewRecord instanceof MockObject
        );

        $saveNewRecord->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                function (
                    QueueRecord $queueRecord
                ) use ($queueModel): Payload {
                    $this->validateQueueRecordAgainstModel(
                        $queueRecord,
                        $queueModel
                    );

                    return new Payload(Payload::STATUS_NOT_CREATED);
                }
            );

        $service = new AddToQueue(
            $transactionManager,
            TestConfig::$di->get(UuidFactoryWithOrderedTimeCodec::class),
            $saveNewRecord,
            new TransformQueueModelToRecord(),
            new TransformQueueItemtoRecord(),
        );

        $payload = $service($queueModel);

        self::assertSame(Payload::STATUS_ERROR, $payload->getStatus());

        self::assertSame(
            ['message' => 'An unknown error occurred'],
            $payload->getResult(),
        );
    }

    public function testWhenItemNotCreated(): void
    {
        $queueModel = $this->createQueueModel();
        $queueItem  = $this->createQueueItemModel();
        $queueModel->addItem($queueItem);

        $transactionManager = $this->createMock(
            DatabaseTransactionManager::class
        );

        assert(
            $transactionManager instanceof DatabaseTransactionManager &&
            $transactionManager instanceof MockObject
        );

        $transactionManager->expects(self::at(0))
            ->method('beginTransaction')
            ->willReturn(true);

        $transactionManager->expects(self::never())
            ->method('commit');

        $transactionManager->expects(self::at(1))
            ->method('rollBack');

        $saveNewRecord = $this->createMock(
            SaveNewRecord::class
        );

        assert(
            $saveNewRecord instanceof SaveNewRecord &&
            $saveNewRecord instanceof MockObject
        );

        $saveNewRecord->expects(self::at(0))
            ->method('__invoke')
            ->willReturnCallback(
                function (
                    QueueRecord $queueRecord
                ) use ($queueModel): Payload {
                    $this->validateQueueRecordAgainstModel(
                        $queueRecord,
                        $queueModel
                    );

                    return new Payload(Payload::STATUS_CREATED);
                }
            );

        $saveNewRecord->expects(self::at(1))
            ->method('__invoke')
            ->willReturnCallback(
                function (
                    QueueItemRecord $queueItemRecord
                ) use (
                    $queueItem,
                    $queueModel
                ): Payload {
                    $this->validateQueueItemRecordAgainstModel(
                        $queueItemRecord,
                        $queueItem,
                        $queueModel,
                    );

                    return new Payload(Payload::STATUS_NOT_FOUND);
                }
            );

        $service = new AddToQueue(
            $transactionManager,
            TestConfig::$di->get(UuidFactoryWithOrderedTimeCodec::class),
            $saveNewRecord,
            new TransformQueueModelToRecord(),
            new TransformQueueItemtoRecord(),
        );

        $payload = $service($queueModel);

        self::assertSame(Payload::STATUS_ERROR, $payload->getStatus());

        self::assertSame(
            ['message' => 'An unknown error occurred'],
            $payload->getResult(),
        );
    }

    public function test(): void
    {
        $queueModel = $this->createQueueModel();
        $queueItem  = $this->createQueueItemModel();
        $queueModel->addItem($queueItem);

        $transactionManager = $this->createMock(
            DatabaseTransactionManager::class
        );

        assert(
            $transactionManager instanceof DatabaseTransactionManager &&
            $transactionManager instanceof MockObject
        );

        $transactionManager->expects(self::at(0))
            ->method('beginTransaction')
            ->willReturn(true);

        $transactionManager->expects(self::never())
            ->method('rollBack');

        $transactionManager->expects(self::at(1))
            ->method('commit');

        $saveNewRecord = $this->createMock(
            SaveNewRecord::class
        );

        assert(
            $saveNewRecord instanceof SaveNewRecord &&
            $saveNewRecord instanceof MockObject
        );

        $saveNewRecord->expects(self::at(0))
            ->method('__invoke')
            ->willReturnCallback(
                function (
                    QueueRecord $queueRecord
                ) use ($queueModel): Payload {
                    $this->validateQueueRecordAgainstModel(
                        $queueRecord,
                        $queueModel
                    );

                    return new Payload(Payload::STATUS_CREATED);
                }
            );

        $saveNewRecord->expects(self::at(1))
            ->method('__invoke')
            ->willReturnCallback(
                function (
                    QueueItemRecord $queueItemRecord
                ) use (
                    $queueItem,
                    $queueModel
                ): Payload {
                    $this->validateQueueItemRecordAgainstModel(
                        $queueItemRecord,
                        $queueItem,
                        $queueModel,
                    );

                    return new Payload(Payload::STATUS_CREATED);
                }
            );

        $service = new AddToQueue(
            $transactionManager,
            TestConfig::$di->get(UuidFactoryWithOrderedTimeCodec::class),
            $saveNewRecord,
            new TransformQueueModelToRecord(),
            new TransformQueueItemtoRecord(),
        );

        $payload = $service($queueModel);

        self::assertSame(Payload::STATUS_SUCCESSFUL, $payload->getStatus());

        self::assertSame([], $payload->getResult());
    }
}
