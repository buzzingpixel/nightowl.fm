<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\Services\PostRun;
use App\Context\Queue\Transformers\TransformQueueItemtoRecord;
use App\Context\Queue\Transformers\TransformQueueModelToRecord;
use App\Payload\Payload;
use App\Persistence\Queue\QueueItemRecord;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\SaveExistingRecord;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

use function assert;

class PostRunTest extends TestCase
{
    public function testWhenAllItemsFinished(): void
    {
        $queueItemModel1             = new QueueItemModel();
        $queueItemModel1->isFinished = true;
        $queueItemModel2             = new QueueItemModel();
        $queueItemModel2->isFinished = true;
        $queueItemModel3             = new QueueItemModel();
        $queueItemModel3->isFinished = true;

        $queueItemRecord = new QueueItemRecord();

        $queueModel = new QueueModel();

        $queueModel->addItem($queueItemModel1);
        $queueModel->addItem($queueItemModel2);
        $queueModel->addItem($queueItemModel3);

        $queueRecord = new QueueRecord();

        $queueModelToRecord = $this->createMock(
            TransformQueueModelToRecord::class
        );

        assert(
            $queueModelToRecord instanceof TransformQueueModelToRecord,
            $queueModelToRecord instanceof MockObject
        );

        $queueModelToRecord->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($queueModel))
            ->willReturn($queueRecord);

        $queueItemToRecord = $this->createMock(
            TransformQueueItemtoRecord::class
        );

        assert(
            $queueItemToRecord instanceof TransformQueueItemtoRecord,
            $queueItemToRecord instanceof MockObject
        );

        $queueItemToRecord->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($queueItemModel3))
            ->willReturn($queueItemRecord);

        $saveExistingRecord = $this->createMock(
            SaveExistingRecord::class
        );

        assert(
            $saveExistingRecord instanceof SaveExistingRecord,
            $saveExistingRecord instanceof MockObject
        );

        $saveExistingRecord->expects(self::at(0))
            ->method('__invoke')
            ->with(self::equalTo($queueRecord))
            ->willReturn(new Payload(Payload::STATUS_UPDATED));

        $saveExistingRecord->expects(self::at(1))
            ->method('__invoke')
            ->with(self::equalTo($queueItemRecord))
            ->willReturn(new Payload(Payload::STATUS_UPDATED));

        $service = new PostRun(
            $queueModelToRecord,
            $queueItemToRecord,
            $saveExistingRecord,
        );

        $now = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC'),
        );

        $service($queueItemModel3);

        self::assertTrue($queueItemModel3->isFinished);

        self::assertSame(
            $now->format(DateTimeInterface::ATOM),
            $queueItemModel3->finishedAt->format(DateTimeInterface::ATOM),
        );

        self::assertFalse($queueModel->isRunning);

        self::assertSame(100.0, $queueModel->percentComplete);

        self::assertTrue($queueModel->isFinished);

        self::assertSame(
            $now->format(DateTimeInterface::ATOM),
            $queueModel->finishedAt->format(DateTimeInterface::ATOM),
        );
    }

    public function testWhenSomeItemsFinished(): void
    {
        $queueItemModel1             = new QueueItemModel();
        $queueItemModel1->isFinished = false;
        $queueItemModel2             = new QueueItemModel();
        $queueItemModel2->isFinished = false;
        $queueItemModel3             = new QueueItemModel();
        $queueItemModel3->isFinished = false;

        $queueItemRecord = new QueueItemRecord();

        $queueModel = new QueueModel();

        $queueModel->addItem($queueItemModel1);
        $queueModel->addItem($queueItemModel2);
        $queueModel->addItem($queueItemModel3);

        $queueRecord = new QueueRecord();

        $queueModelToRecord = $this->createMock(
            TransformQueueModelToRecord::class
        );

        assert(
            $queueModelToRecord instanceof TransformQueueModelToRecord,
            $queueModelToRecord instanceof MockObject
        );

        $queueModelToRecord->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($queueModel))
            ->willReturn($queueRecord);

        $queueItemToRecord = $this->createMock(
            TransformQueueItemtoRecord::class
        );

        assert(
            $queueItemToRecord instanceof TransformQueueItemtoRecord,
            $queueItemToRecord instanceof MockObject
        );

        $queueItemToRecord->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($queueItemModel1))
            ->willReturn($queueItemRecord);

        $saveExistingRecord = $this->createMock(
            SaveExistingRecord::class
        );

        assert(
            $saveExistingRecord instanceof SaveExistingRecord,
            $saveExistingRecord instanceof MockObject
        );

        $saveExistingRecord->expects(self::at(0))
            ->method('__invoke')
            ->with(self::equalTo($queueRecord))
            ->willReturn(new Payload(Payload::STATUS_UPDATED));

        $saveExistingRecord->expects(self::at(1))
            ->method('__invoke')
            ->with(self::equalTo($queueItemRecord))
            ->willReturn(new Payload(Payload::STATUS_UPDATED));

        $service = new PostRun(
            $queueModelToRecord,
            $queueItemToRecord,
            $saveExistingRecord,
        );

        $now = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC'),
        );

        $service($queueItemModel1);

        self::assertTrue($queueItemModel1->isFinished);
        self::assertFalse($queueItemModel2->isFinished);
        self::assertFalse($queueItemModel3->isFinished);

        self::assertSame(
            $now->format(DateTimeInterface::ATOM),
            $queueItemModel1->finishedAt->format(DateTimeInterface::ATOM),
        );

        self::assertFalse($queueModel->isRunning);

        self::assertSame(33.333333333333, $queueModel->percentComplete);

        self::assertFalse($queueModel->isFinished);
    }
}
