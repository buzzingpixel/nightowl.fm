<?php

declare(strict_types=1);

namespace Tests\Context\Schedule\Services;

use App\Context\Schedule\Frequency;
use App\Context\Schedule\Services\FetchSchedules;
use App\Context\Schedule\Transformers\TransformRecordToModel;
use App\Persistence\Constants;
use App\Persistence\Schedule\ScheduleTrackingRecord;
use Config\Schedule;
use DateTimeInterface;
use Exception;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;
use Tests\TestConfig;

class FetchSchedulesTest extends TestCase
{
    private FetchSchedules $service;

    /** @var Schedule&MockObject */
    private $schedule;
    /** @var PDO&MockObject */
    private $pdo;
    /** @var PDOStatement&MockObject */
    private $pdoStatement;

    public function testWhenFetchScheduleThrowsException(): void
    {
        $this->schedule->expects(self::once())
            ->method('getScheduleClasses')
            ->willThrowException(new Exception());

        $this->pdo->expects(self::never())
            ->method(self::anything());

        $payload = ($this->service)();

        self::assertSame([], $payload->getSchedules());
    }

    public function testWhenNoSchedules(): void
    {
        $this->schedule->expects(self::once())
            ->method('getScheduleClasses')
            ->willReturn([]);

        $this->pdo->expects(self::never())
            ->method(self::anything());

        $this->pdoStatement->expects(self::never())
            ->method(self::anything());

        $payload = ($this->service)();

        self::assertSame([], $payload->getSchedules());
    }

    public function testWhenQueryReturnsNoResults(): void
    {
        $classes = [
            'Foo\Bar\Test\Class\One',
            'Foo\Bar\Test\Class\Two',
        ];

        $this->schedule->expects(self::once())
            ->method('getScheduleClasses')
            ->willReturn($classes);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'SELECT * FROM schedule_tracking WHERE class IN (?,?)'
            ))
            ->willReturn($this->pdoStatement);

        $this->pdoStatement->expects(self::at(0))
            ->method('execute')
            ->with(self::equalTo($classes))
            ->willReturn(true);

        $this->pdoStatement->expects(self::at(1))
            ->method('fetchAll')
            ->with(
                self::equalTo(PDO::FETCH_CLASS),
                self::equalTo(ScheduleTrackingRecord::class)
            )
            ->willReturn([]);

        $payload = ($this->service)();

        $schedules = $payload->getSchedules();

        self::assertCount(2, $schedules);

        $schedule1 = $schedules[0];
        self::assertSame('', $schedule1->id);
        self::assertSame('Foo\Bar\Test\Class\One', $schedule1->class);
        self::assertSame(Frequency::ALWAYS, $schedule1->runEvery);
        self::assertFalse($schedule1->isRunning);
        self::assertNull($schedule1->lastRunStartAt);
        self::assertNull($schedule1->lastRunEndAt);

        $schedule2 = $schedules[1];
        self::assertSame('', $schedule2->id);
        self::assertSame('Foo\Bar\Test\Class\Two', $schedule2->class);
        self::assertSame(Frequency::ALWAYS, $schedule2->runEvery);
        self::assertFalse($schedule2->isRunning);
        self::assertNull($schedule2->lastRunStartAt);
        self::assertNull($schedule2->lastRunEndAt);
    }

    public function testWhenQueryHasOneResultAndOneNoMatch(): void
    {
        $classes = [
            'Foo\Bar\Test\Class\One',
            'Foo\Bar\Test\Class\Two',
        ];

        $this->schedule->expects(self::once())
            ->method('getScheduleClasses')
            ->willReturn($classes);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'SELECT * FROM schedule_tracking WHERE class IN (?,?)'
            ))
            ->willReturn($this->pdoStatement);

        $this->pdoStatement->expects(self::at(0))
            ->method('execute')
            ->with(self::equalTo($classes))
            ->willReturn(true);

        $record1                    = new ScheduleTrackingRecord();
        $record1->id                = 'FooBarID';
        $record1->class             = 'Foo\Bar\Test\Class\One';
        $record1->is_running        = '1';
        $record1->last_run_start_at = '2019-11-30 18:56:43+00';
        $record1->last_run_end_at   = '2017-11-30 18:56:43+00';

        $record2                    = new ScheduleTrackingRecord();
        $record1->id                = 'BarBazId';
        $record2->class             = 'Foo\Bar';
        $record2->is_running        = '0';
        $record2->last_run_start_at = '2016-11-30 18:56:43+00';
        $record2->last_run_end_at   = '2016-11-30 18:56:43+00';

        $this->pdoStatement->expects(self::at(1))
            ->method('fetchAll')
            ->with(
                self::equalTo(PDO::FETCH_CLASS),
                self::equalTo(ScheduleTrackingRecord::class)
            )
            ->willReturn([$record1, $record2]);

        $payload = ($this->service)();

        $schedules = $payload->getSchedules();

        self::assertCount(2, $schedules);

        $schedule1 = $schedules[0];
        self::assertSame('BarBazId', $schedule1->id);
        self::assertSame('Foo\Bar\Test\Class\One', $schedule1->class);
        self::assertSame(Frequency::ALWAYS, $schedule1->runEvery);
        self::assertTrue($schedule1->isRunning);

        $lastRunTestTime = DateTimeImmutable::createFromFormat(
            Constants::POSTGRES_OUTPUT_FORMAT,
            $record1->last_run_start_at
        );

        $lastRunStartAt = $schedule1->lastRunStartAt;
        self::assertInstanceOf(DateTimeImmutable::class, $lastRunStartAt);
        self::assertSame(
            $lastRunTestTime->format(DateTimeInterface::ATOM),
            $lastRunStartAt->format(DateTimeInterface::ATOM)
        );

        $lastRunEndTestTime = DateTimeImmutable::createFromFormat(
            Constants::POSTGRES_OUTPUT_FORMAT,
            $record1->last_run_end_at
        );

        $lastRunEndAt = $schedule1->lastRunEndAt;
        self::assertInstanceOf(DateTimeImmutable::class, $lastRunEndAt);
        self::assertSame(
            $lastRunEndTestTime->format(DateTimeInterface::ATOM),
            $lastRunEndAt->format(DateTimeInterface::ATOM)
        );

        $schedule2 = $schedules[1];
        self::assertSame('', $schedule2->id);
        self::assertSame('Foo\Bar\Test\Class\Two', $schedule2->class);
        self::assertSame(Frequency::ALWAYS, $schedule2->runEvery);
        self::assertFalse($schedule2->isRunning);
        self::assertNull($schedule2->lastRunStartAt);
        self::assertNull($schedule2->lastRunEndAt);
    }

    protected function setUp(): void
    {
        $this->schedule = $this->createMock(Schedule::class);

        $this->pdo = $this->createMock(PDO::class);

        $this->pdoStatement = $this->createMock(
            PDOStatement::class
        );

        $this->service = new FetchSchedules(
            $this->schedule,
            $this->pdo,
            TestConfig::$di->get(
                TransformRecordToModel::class
            )
        );
    }
}
