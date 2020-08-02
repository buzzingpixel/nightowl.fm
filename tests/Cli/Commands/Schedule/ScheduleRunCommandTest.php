<?php

declare(strict_types=1);

namespace Tests\Cli\Commands\Schedule;

use App\Cli\Commands\Schedule\ScheduleRunCommand;
use App\Context\Schedule\Models\ScheduleItemModel;
use App\Context\Schedule\Payloads\SchedulesPayload;
use App\Context\Schedule\Services\CheckIfModelShouldRun;
use App\Context\Schedule\Services\FetchSchedules;
use App\Context\Schedule\Services\SaveSchedule;
use App\Payload\Payload;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Safe\DateTimeImmutable;
use stdClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ScheduleRunCommandTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testWhenNoSchedule(): void
    {
        $command = new ScheduleRunCommand(
            $this->createMock(ContainerInterface::class),
            $this->mockFetchSchedules(),
            $this->createMock(CheckIfModelShouldRun::class),
            $this->createMock(SaveSchedule::class)
        );

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::once())
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=yellow>There are no scheduled commands set up</>'
            ));

        $return = $command->execute(
            $this->createMock(InputInterface::class),
            $output
        );

        self::assertSame(0, $return);
    }

    /**
     * @throws Throwable
     */
    public function testWhenSaveScheduleThrowsException(): void
    {
        $shouldRun = $this->createMock(CheckIfModelShouldRun::class);
        $shouldRun->method('check')->willReturn(true);

        $scheduleItemModel = new ScheduleItemModel();

        $schedulesPayload = new SchedulesPayload([
            'schedules' => [$scheduleItemModel],
        ]);

        $saveSchedule = $this->createMock(SaveSchedule::class);

        $saveSchedule->method(self::anything())
            ->willThrowException(new Exception());

        $command = new ScheduleRunCommand(
            $this->createMock(ContainerInterface::class),
            $this->mockFetchSchedules($schedulesPayload),
            $shouldRun,
            $saveSchedule
        );

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::once())
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=red>An unknown error occurred</>'
            ));

        $return = $command->execute(
            $this->createMock(InputInterface::class),
            $output
        );

        self::assertSame(1, $return);
    }

    /**
     * @throws Throwable
     */
    public function testWhenCalledClassThrowsException(): void
    {
        $shouldRun = $this->createMock(CheckIfModelShouldRun::class);
        $shouldRun->method('check')->willReturn(true);

        $scheduleItemModel        = new ScheduleItemModel();
        $scheduleItemModel->class = 'FooBarTestClass';

        $schedulesPayload = new SchedulesPayload([
            'schedules' => [$scheduleItemModel],
        ]);

        $saveSchedule = $this->createMock(SaveSchedule::class);

        $saveSchedule->expects(self::at(0))
            ->method('__invoke')
            ->willReturnCallback(static function (ScheduleItemModel $model) use (
                $scheduleItemModel
            ): Payload {
                self::assertSame($scheduleItemModel, $model);

                self::assertTrue($model->isRunning);

                $currentDateTime = new DateTimeImmutable(
                    'now',
                    new DateTimeZone('UTC')
                );

                $currentDateTimeFormat = $currentDateTime->format(
                    'Y-m-d H:i'
                );

                $modelDateTime = $model->lastRunStartAt;

                self::assertInstanceOf(DateTimeImmutable::class, $modelDateTime);

                $modelDateTimeFormat = $modelDateTime->format(
                    'Y-m-d H:i'
                );

                self::assertSame(
                    $currentDateTimeFormat,
                    $modelDateTimeFormat
                );

                self::assertNull($model->lastRunEndAt);

                return new Payload(Payload::STATUS_SUCCESSFUL);
            });

        $saveSchedule->expects(self::at(1))
            ->method('__invoke')
            ->willReturnCallback(static function (ScheduleItemModel $model) use (
                $scheduleItemModel
            ): Payload {
                self::assertSame($scheduleItemModel, $model);

                self::assertFalse($model->isRunning);

                $currentDateTime = new DateTimeImmutable(
                    'now',
                    new DateTimeZone('UTC')
                );

                $currentDateTimeFormat = $currentDateTime->format(
                    'Y-m-d H:i'
                );

                $modelDateTime = $model->lastRunStartAt;

                self::assertInstanceOf(DateTimeImmutable::class, $modelDateTime);

                $modelDateTimeFormat = $modelDateTime->format(
                    'Y-m-d H:i'
                );

                self::assertSame(
                    $currentDateTimeFormat,
                    $modelDateTimeFormat
                );

                self::assertNull($model->lastRunEndAt);

                return new Payload(Payload::STATUS_SUCCESSFUL);
            });

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo('FooBarTestClass'))
            ->willReturn(new class () {
                public function __invoke(): void
                {
                    throw new Exception('FooBarTestMessage');
                }
            });

        $command = new ScheduleRunCommand(
            $di,
            $this->mockFetchSchedules($schedulesPayload),
            $shouldRun,
            $saveSchedule
        );

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=red>There was a problem running a scheduled command</>'
            ));

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=red>FooBarTestClass</>'
            ));

        $output->expects(self::at(2))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=red>Message: FooBarTestMessage</>'
            ));

        $return = $command->execute(
            $this->createMock(InputInterface::class),
            $output
        );

        self::assertSame(0, $return);
    }

    /**
     * @throws Throwable
     */
    public function testSuccessfulRun(): void
    {
        $shouldRun = $this->createMock(CheckIfModelShouldRun::class);
        $shouldRun->method('check')->willReturn(true);

        $scheduleItemModel        = new ScheduleItemModel();
        $scheduleItemModel->class = 'FooBarTestClass';

        $schedulesPayload = new SchedulesPayload([
            'schedules' => [$scheduleItemModel],
        ]);

        $saveSchedule = $this->createMock(SaveSchedule::class);

        $saveSchedule->expects(self::at(0))
            ->method('__invoke')
            ->willReturnCallback(static function (ScheduleItemModel $model) use (
                $scheduleItemModel
            ): Payload {
                self::assertSame($scheduleItemModel, $model);

                self::assertTrue($model->isRunning);

                $currentDateTime = new DateTimeImmutable(
                    'now',
                    new DateTimeZone('UTC')
                );

                $currentDateTimeFormat = $currentDateTime->format(
                    'Y-m-d H:i'
                );

                $modelDateTime = $model->lastRunStartAt;

                self::assertInstanceOf(DateTimeImmutable::class, $modelDateTime);

                $modelDateTimeFormat = $modelDateTime->format(
                    'Y-m-d H:i'
                );

                self::assertSame(
                    $currentDateTimeFormat,
                    $modelDateTimeFormat
                );

                self::assertNull($model->lastRunEndAt);

                return new Payload(Payload::STATUS_SUCCESSFUL);
            });

        $saveSchedule->expects(self::at(1))
            ->method('__invoke')
            ->willReturnCallback(static function (ScheduleItemModel $model) use (
                $scheduleItemModel
            ): Payload {
                self::assertSame($scheduleItemModel, $model);

                self::assertFalse($model->isRunning);

                $currentDateTime = new DateTimeImmutable(
                    'now',
                    new DateTimeZone('UTC')
                );

                $currentDateTimeFormat = $currentDateTime->format(
                    'Y-m-d H:i'
                );

                $modelDateTime = $model->lastRunStartAt;

                self::assertInstanceOf(DateTimeImmutable::class, $modelDateTime);

                $modelDateTimeFormat = $modelDateTime->format(
                    'Y-m-d H:i'
                );

                self::assertSame(
                    $currentDateTimeFormat,
                    $modelDateTimeFormat
                );

                $lastRun = $model->lastRunEndAt;

                self::assertInstanceOf(DateTimeImmutable::class, $lastRun);

                $lastRunFormat = $lastRun->format('Y-m-d H:i');

                self::assertSame(
                    $modelDateTimeFormat,
                    $lastRunFormat
                );

                return new Payload(Payload::STATUS_SUCCESSFUL);
            });

        $holder = new stdClass();

        $holder->hasRun = false;

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo('FooBarTestClass'))
            ->willReturn(new class ($holder) {
                private stdClass $holder;

                public function __construct(stdClass $holder)
                {
                    $this->holder = $holder;
                }

                public function __invoke(): void
                {
                    $this->holder->hasRun = true;
                }
            });

        $command = new ScheduleRunCommand(
            $di,
            $this->mockFetchSchedules($schedulesPayload),
            $shouldRun,
            $saveSchedule
        );

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::once())
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=green>FooBarTestClass ran successfully</>'
            ));

        $return = $command->execute(
            $this->createMock(InputInterface::class),
            $output
        );

        self::assertSame(0, $return);

        self::assertTrue($holder->hasRun);
    }

    /**
     * @throws Throwable
     */
    public function testModelIsRunningAndShouldRun(): void
    {
        $shouldRun = $this->createMock(CheckIfModelShouldRun::class);
        $shouldRun->method('check')->willReturn(true);

        $scheduleItemModel            = new ScheduleItemModel();
        $scheduleItemModel->class     = 'FooBarClass';
        $scheduleItemModel->isRunning = true;

        $schedulesPayload = new SchedulesPayload([
            'schedules' => [$scheduleItemModel],
        ]);

        $saveSchedule = $this->createMock(SaveSchedule::class);

        $saveSchedule->expects(self::never())
            ->method(self::anything());

        $command = new ScheduleRunCommand(
            $di  = $this->createMock(ContainerInterface::class),
            $this->mockFetchSchedules($schedulesPayload),
            $shouldRun,
            $saveSchedule
        );

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::once())
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=yellow>FooBarClass is currently running</>'
            ));

        $return = $command->execute(
            $this->createMock(InputInterface::class),
            $output
        );

        self::assertSame(0, $return);
    }

    /**
     * @throws Throwable
     */
    public function testModelShouldNotRun(): void
    {
        $shouldRun = $this->createMock(CheckIfModelShouldRun::class);
        $shouldRun->method('check')->willReturn(false);

        $scheduleItemModel        = new ScheduleItemModel();
        $scheduleItemModel->class = 'FooBarClass';

        $schedulesPayload = new SchedulesPayload([
            'schedules' => [$scheduleItemModel],
        ]);

        $saveSchedule = $this->createMock(SaveSchedule::class);

        $saveSchedule->expects(self::never())
            ->method(self::anything());

        $command = new ScheduleRunCommand(
            $di  = $this->createMock(ContainerInterface::class),
            $this->mockFetchSchedules($schedulesPayload),
            $shouldRun,
            $saveSchedule
        );

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::once())
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=green>FooBarClass does not need to run at this time</>'
            ));

        $return = $command->execute(
            $this->createMock(InputInterface::class),
            $output
        );

        self::assertSame(0, $return);
    }

    /**
     * @return FetchSchedules&MockObject
     *
     * @throws Throwable
     */
    private function mockFetchSchedules(?SchedulesPayload $schedulesPayload = null): FetchSchedules
    {
        $mock = $this->createMock(FetchSchedules::class);

        if ($schedulesPayload === null) {
            $schedulesPayload = new SchedulesPayload();
        }

        $mock->expects(self::once())
            ->method('__invoke')
            ->willReturn($schedulesPayload);

        return $mock;
    }
}
