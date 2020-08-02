<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\Services\MarkItemAsStarted;
use App\Context\Queue\Transformers\TransformQueueModelToRecord;
use App\Payload\Payload;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\SaveExistingRecord;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;
use Tests\TestConfig;
use Throwable;

use function assert;

class MarkItemAsStartedTest extends TestCase
{
    public function testWhenUpdated(): void
    {
        $assumeDeadAfter = new DateTimeImmutable(
            '10 years ago',
            new DateTimeZone('UTC')
        );

        $newAssumeDeadAfter = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        $addedAt = new DateTimeImmutable(
            '15 years ago',
            new DateTimeZone('UTC')
        );

        $finishedAt = new DateTimeImmutable(
            '2 years ago',
            new DateTimeZone('UTC')
        );

        $newAssumeDeadAfter = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        $diff = $addedAt->diff($assumeDeadAfter);

        $newAssumeDeadAfter = $newAssumeDeadAfter->add($diff);

        $model                         = new QueueModel();
        $model->id                     = 'modelId';
        $model->handle                 = 'modelHandle';
        $model->displayName            = 'modelDisplayName';
        $model->hasStarted             = false;
        $model->isRunning              = false;
        $model->assumeDeadAfter        = $assumeDeadAfter;
        $model->initialAssumeDeadAfter = $assumeDeadAfter;
        $model->isFinished             = false;
        $model->finishedDueToError     = false;
        $model->errorMessage           = 'modelErrorMessage';
        $model->percentComplete        = 3.2;
        $model->addedAt                = $addedAt;
        $model->finishedAt             = $finishedAt;

        $saveExistingRecord = $this->createMock(
            SaveExistingRecord::class
        );

        $saveExistingRecord->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (QueueRecord $record) use (
                    $assumeDeadAfter,
                    $addedAt,
                    $finishedAt,
                    $newAssumeDeadAfter
                ): Payload {
                    self::assertSame(
                        'modelId',
                        $record->id
                    );

                    self::assertSame(
                        'modelHandle',
                        $record->handle
                    );

                    self::assertSame(
                        'modelDisplayName',
                        $record->display_name
                    );

                    self::assertSame(
                        '1',
                        $record->has_started
                    );

                    self::assertSame(
                        '1',
                        $record->is_running
                    );

                    self::assertSame(
                        $newAssumeDeadAfter->format(
                            DateTimeInterface::ATOM
                        ),
                        $record->assume_dead_after,
                    );

                    self::assertSame(
                        $assumeDeadAfter->format(
                            DateTimeInterface::ATOM
                        ),
                        $record->initial_assume_dead_after,
                    );

                    self::assertSame(
                        '0',
                        $record->is_finished
                    );

                    self::assertSame(
                        '0',
                        $record->finished_due_to_error
                    );

                    self::assertSame(
                        'modelErrorMessage',
                        $record->error_message
                    );

                    self::assertSame(
                        3.2,
                        $record->percent_complete
                    );

                    self::assertSame(
                        $addedAt->format(
                            DateTimeInterface::ATOM
                        ),
                        $record->added_at,
                    );

                    self::assertSame(
                        $finishedAt->format(
                            DateTimeInterface::ATOM
                        ),
                        $record->finished_at,
                    );

                    return new Payload(Payload::STATUS_UPDATED);
                }
            );

        assert(
            $saveExistingRecord instanceof SaveExistingRecord,
            $saveExistingRecord instanceof MockObject
        );

        $service = new MarkItemAsStarted(
            TestConfig::$di->get(TransformQueueModelToRecord::class),
            $saveExistingRecord
        );

        $service($model);

        self::assertTrue($model->hasStarted);
        self::assertTrue($model->isRunning);
    }

    public function testWhenNotUpdated(): void
    {
        $assumeDeadAfter = new DateTimeImmutable(
            '10 years ago',
            new DateTimeZone('UTC')
        );

        $newAssumeDeadAfter = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        $addedAt = new DateTimeImmutable(
            '15 years ago',
            new DateTimeZone('UTC')
        );

        $finishedAt = new DateTimeImmutable(
            '2 years ago',
            new DateTimeZone('UTC')
        );

        $newAssumeDeadAfter = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        $diff = $addedAt->diff($assumeDeadAfter);

        $newAssumeDeadAfter = $newAssumeDeadAfter->add($diff);

        $model                         = new QueueModel();
        $model->id                     = 'modelId';
        $model->handle                 = 'modelHandle';
        $model->displayName            = 'modelDisplayName';
        $model->hasStarted             = false;
        $model->isRunning              = false;
        $model->assumeDeadAfter        = $assumeDeadAfter;
        $model->initialAssumeDeadAfter = $assumeDeadAfter;
        $model->isFinished             = false;
        $model->finishedDueToError     = false;
        $model->errorMessage           = 'modelErrorMessage';
        $model->percentComplete        = 3.2;
        $model->addedAt                = $addedAt;
        $model->finishedAt             = $finishedAt;

        $saveExistingRecord = $this->createMock(
            SaveExistingRecord::class
        );

        $saveExistingRecord->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (QueueRecord $record) use (
                    $assumeDeadAfter,
                    $addedAt,
                    $finishedAt,
                    $newAssumeDeadAfter
                ): Payload {
                    self::assertSame(
                        'modelId',
                        $record->id
                    );

                    self::assertSame(
                        'modelHandle',
                        $record->handle
                    );

                    self::assertSame(
                        'modelDisplayName',
                        $record->display_name
                    );

                    self::assertSame(
                        '1',
                        $record->has_started
                    );

                    self::assertSame(
                        '1',
                        $record->is_running
                    );

                    self::assertSame(
                        $newAssumeDeadAfter->format(
                            DateTimeInterface::ATOM
                        ),
                        $record->assume_dead_after,
                    );

                    self::assertSame(
                        $assumeDeadAfter->format(
                            DateTimeInterface::ATOM
                        ),
                        $record->initial_assume_dead_after,
                    );

                    self::assertSame(
                        '0',
                        $record->is_finished
                    );

                    self::assertSame(
                        '0',
                        $record->finished_due_to_error
                    );

                    self::assertSame(
                        'modelErrorMessage',
                        $record->error_message
                    );

                    self::assertSame(
                        3.2,
                        $record->percent_complete
                    );

                    self::assertSame(
                        $addedAt->format(
                            DateTimeInterface::ATOM
                        ),
                        $record->added_at,
                    );

                    self::assertSame(
                        $finishedAt->format(
                            DateTimeInterface::ATOM
                        ),
                        $record->finished_at,
                    );

                    return new Payload(Payload::STATUS_ERROR);
                }
            );

        assert(
            $saveExistingRecord instanceof SaveExistingRecord,
            $saveExistingRecord instanceof MockObject
        );

        $service = new MarkItemAsStarted(
            TestConfig::$di->get(TransformQueueModelToRecord::class),
            $saveExistingRecord
        );

        $exception = null;

        try {
            $service($model);
        } catch (Throwable $e) {
            $exception = $e;
        }

        assert($exception instanceof Exception);

        $msg = 'An unknown error occurred';

        if ($exception->getMessage() !== $msg) {
            throw $exception;
        }

        self::assertSame(
            'An unknown error occurred',
            $exception->getMessage(),
        );

        self::assertTrue($model->hasStarted);
        self::assertTrue($model->isRunning);
    }
}
