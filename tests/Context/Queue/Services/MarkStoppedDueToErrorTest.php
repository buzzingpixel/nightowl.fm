<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\Services\MarkStoppedDueToError;
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

use function assert;
use function get_class;

use const PHP_EOL;

class MarkStoppedDueToErrorTest extends TestCase
{
    public function testWhenExceptionIsNull(): void
    {
        $queueModel              = new QueueModel();
        $queueModel->id          = 'testId';
        $queueModel->handle      = 'testHandle';
        $queueModel->displayName = 'testDisplayName';
        $queueModel->hasStarted  = true;
        $queueModel->isRunning   = true;

        $saveExistingRecord = $this->createMock(
            SaveExistingRecord::class
        );

        assert(
            $saveExistingRecord instanceof SaveExistingRecord &&
            $saveExistingRecord instanceof MockObject
        );

        $now = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        $saveExistingRecord->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (QueueRecord $record) use ($now): Payload {
                    self::assertSame('testId', $record->id);
                    self::assertSame('testHandle', $record->handle);
                    self::assertSame('testDisplayName', $record->display_name);
                    self::assertSame('1', $record->has_started);
                    self::assertSame('0', $record->is_running);
                    self::assertSame('1', $record->is_finished);
                    self::assertSame('1', $record->finished_due_to_error);
                    self::assertSame('', $record->error_message);
                    self::assertSame(
                        $now->format(DateTimeInterface::ATOM),
                        $record->finished_at,
                    );

                    return new Payload(Payload::STATUS_NOT_UPDATED);
                }
            );

        $service = new MarkStoppedDueToError(
            TestConfig::$di->get(TransformQueueModelToRecord::class),
            $saveExistingRecord
        );

        $service($queueModel);

        self::assertFalse($queueModel->isRunning);
        self::assertTrue($queueModel->isFinished);
        self::assertTrue($queueModel->finishedDueToError);
        self::assertSame('', $queueModel->errorMessage);
        self::assertSame(
            $now->format(DateTimeInterface::ATOM),
            $queueModel->finishedAt->format(DateTimeInterface::ATOM),
        );
    }

    public function test(): void
    {
        $e = new Exception(
            'TestMessage',
            345,
        );

        $eol  = PHP_EOL . PHP_EOL;
        $msg  = '';
        $msg .= 'Exception Type: ' . get_class($e) . $eol;
        $msg .= 'Error Code: ' . $e->getCode() . $eol;
        $msg .= 'File: ' . $e->getFile() . $eol;
        $msg .= 'Line: ' . $e->getLine() . $eol;
        $msg .= 'Message: ' . $e->getMessage() . $eol;
        $msg .= 'Trace . ' . $e->getTraceAsString();

        $queueModel              = new QueueModel();
        $queueModel->id          = 'testId';
        $queueModel->handle      = 'testHandle';
        $queueModel->displayName = 'testDisplayName';
        $queueModel->hasStarted  = true;
        $queueModel->isRunning   = true;

        $saveExistingRecord = $this->createMock(
            SaveExistingRecord::class
        );

        assert(
            $saveExistingRecord instanceof SaveExistingRecord &&
            $saveExistingRecord instanceof MockObject
        );

        $now = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        $saveExistingRecord->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (QueueRecord $record) use ($now, $msg): Payload {
                    self::assertSame('testId', $record->id);
                    self::assertSame('testHandle', $record->handle);
                    self::assertSame('testDisplayName', $record->display_name);
                    self::assertSame('1', $record->has_started);
                    self::assertSame('0', $record->is_running);
                    self::assertSame('1', $record->is_finished);
                    self::assertSame('1', $record->finished_due_to_error);
                    self::assertSame($msg, $record->error_message);
                    self::assertSame(
                        $now->format(DateTimeInterface::ATOM),
                        $record->finished_at,
                    );

                    return new Payload(Payload::STATUS_NOT_UPDATED);
                }
            );

        $service = new MarkStoppedDueToError(
            TestConfig::$di->get(TransformQueueModelToRecord::class),
            $saveExistingRecord
        );

        $service($queueModel, $e);

        self::assertFalse($queueModel->isRunning);
        self::assertTrue($queueModel->isFinished);
        self::assertTrue($queueModel->finishedDueToError);
        self::assertSame($msg, $queueModel->errorMessage);
        self::assertSame(
            $now->format(DateTimeInterface::ATOM),
            $queueModel->finishedAt->format(DateTimeInterface::ATOM),
        );
    }
}
