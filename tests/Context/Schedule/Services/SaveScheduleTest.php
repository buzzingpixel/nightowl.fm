<?php

declare(strict_types=1);

namespace Tests\Context\Schedule\Services;

use App\Context\Schedule\Models\ScheduleItemModel;
use App\Context\Schedule\Services\SaveSchedule;
use App\Context\Schedule\Transformers\TransformModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use App\Persistence\SaveNewRecord;
use App\Persistence\Schedule\ScheduleTrackingRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use DateTimeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;
use stdClass;
use Tests\TestConfig;
use Throwable;

use function assert;
use function Safe\strtotime;

class SaveScheduleTest extends TestCase
{
    private SaveSchedule $service;

    /** @var SaveNewRecord&MockObject */
    private $saveNewRecord;
    /** @var SaveExistingRecord&MockObject */
    private $saveExistingRecord;

    public function testWhenNoClass(): void
    {
        $this->saveNewRecord->expects(self::never())
            ->method(self::anything());

        $this->saveExistingRecord->expects(self::never())
            ->method(self::anything());

        $model = new ScheduleItemModel();

        $payload = ($this->service)($model);

        self::assertSame(
            Payload::STATUS_NOT_VALID,
            $payload->getStatus()
        );

        self::assertSame(
            ['message' => 'Class is required'],
            $payload->getResult()
        );
    }

    public function testWhenSaveNewRecordStatusIsNotCreated(): void
    {
        $saveRecordHolder = new stdClass();

        $saveRecordHolder->arg = null;

        $this->saveNewRecord->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (ScheduleTrackingRecord $record) use (
                    $saveRecordHolder
                ) {
                    $saveRecordHolder->arg = $record;

                    return new Payload(Payload::STATUS_NOT_CREATED);
                }
            );

        $this->saveExistingRecord->expects(self::never())
            ->method(self::anything());

        $model        = new ScheduleItemModel();
        $model->class = 'FooBarClass';

        $payload = ($this->service)($model);

        self::assertSame(
            Payload::STATUS_ERROR,
            $payload->getStatus()
        );

        self::assertSame(
            ['message' => 'An unknown error occurred'],
            $payload->getResult()
        );

        $record = $saveRecordHolder->arg;
        assert($record instanceof ScheduleTrackingRecord || $record === null);

        self::assertInstanceOf(ScheduleTrackingRecord::class, $record);

        self::assertNotEmpty($record->id);

        self::assertSame('0', $record->is_running);

        self::assertSame('', $record->last_run_start_at);

        self::assertSame('', $record->last_run_end_at);
    }

    /**
     * @throws Throwable
     */
    public function testSaveNewRecord(): void
    {
        $saveRecordHolder = new stdClass();

        $saveRecordHolder->arg = null;

        $this->saveNewRecord->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (ScheduleTrackingRecord $record) use (
                    $saveRecordHolder
                ) {
                    $saveRecordHolder->arg = $record;

                    return new Payload(Payload::STATUS_CREATED);
                }
            );

        $this->saveExistingRecord->expects(self::never())
            ->method(self::anything());

        $lastRunStartAt = (new DateTimeImmutable())->setTimestamp(
            strtotime('3 days ago')
        );

        $lastRunEndAt = (new DateTimeImmutable())->setTimestamp(
            strtotime('120 days ago')
        );

        $model                 = new ScheduleItemModel();
        $model->class          = 'FooBarClass';
        $model->isRunning      = true;
        $model->lastRunStartAt = $lastRunStartAt;
        $model->lastRunEndAt   = $lastRunEndAt;

        $payload = ($this->service)($model);

        self::assertSame(
            Payload::STATUS_CREATED,
            $payload->getStatus()
        );

        $record = $saveRecordHolder->arg;
        assert($record instanceof ScheduleTrackingRecord || $record === null);

        self::assertInstanceOf(ScheduleTrackingRecord::class, $record);

        self::assertNotEmpty($record->id);

        self::assertSame('1', $record->is_running);

        self::assertSame(
            $lastRunStartAt->format(DateTimeInterface::ATOM),
            $record->last_run_start_at
        );

        self::assertSame(
            $lastRunEndAt->format(DateTimeInterface::ATOM),
            $record->last_run_end_at
        );
    }

    /**
     * @throws Throwable
     */
    public function testSaveExistingRecord(): void
    {
        $saveRecordHolder = new stdClass();

        $saveRecordHolder->arg = null;

        $this->saveNewRecord->expects(self::never())
            ->method(self::anything());

        $this->saveExistingRecord->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (ScheduleTrackingRecord $record) use (
                    $saveRecordHolder
                ) {
                    $saveRecordHolder->arg = $record;

                    return new Payload(Payload::STATUS_UPDATED);
                }
            );

        $lastRunStartAt = (new DateTimeImmutable())->setTimestamp(
            strtotime('3 days ago')
        );

        $lastRunEndAt = (new DateTimeImmutable())->setTimestamp(
            strtotime('120 days ago')
        );

        $model                 = new ScheduleItemModel();
        $model->id             = 'FooBarId';
        $model->class          = 'FooBarClass';
        $model->isRunning      = true;
        $model->lastRunStartAt = $lastRunStartAt;
        $model->lastRunEndAt   = $lastRunEndAt;

        $payload = ($this->service)($model);

        self::assertSame(
            Payload::STATUS_UPDATED,
            $payload->getStatus()
        );

        $record = $saveRecordHolder->arg;
        assert($record instanceof ScheduleTrackingRecord || $record === null);

        self::assertInstanceOf(ScheduleTrackingRecord::class, $record);

        self::assertSame('FooBarId', $record->id);

        self::assertSame('1', $record->is_running);

        self::assertSame(
            $lastRunStartAt->format(DateTimeInterface::ATOM),
            $record->last_run_start_at
        );

        self::assertSame(
            $lastRunEndAt->format(DateTimeInterface::ATOM),
            $record->last_run_end_at
        );
    }

    protected function setUp(): void
    {
        $this->saveNewRecord = $this->createMock(
            SaveNewRecord::class
        );

        $this->saveExistingRecord = $this->createMock(
            SaveExistingRecord::class
        );

        $this->service = new SaveSchedule(
            TestConfig::$di->get(
                TransformModelToRecord::class
            ),
            $this->saveNewRecord,
            $this->saveExistingRecord,
            TestConfig::$di->get(
                UuidFactoryWithOrderedTimeCodec::class
            )
        );
    }
}
