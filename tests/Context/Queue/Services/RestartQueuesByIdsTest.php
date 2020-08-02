<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Services\RestartQueuesByIds;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\Queue\QueueRecord;
use App\Persistence\RecordQuery;
use App\Persistence\RecordQueryFactory;
use App\Persistence\SaveExistingRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function assert;

class RestartQueuesByIdsTest extends TestCase
{
    public function testWhenNoIds(): void
    {
        $recordQueryFactory = $this->createMock(RecordQueryFactory::class);

        assert(
            $recordQueryFactory instanceof RecordQueryFactory &&
            $recordQueryFactory instanceof MockObject
        );

        $recordQueryFactory->expects(self::never())->method(self::anything());

        $saveExistingRecord = $this->createMock(SaveExistingRecord::class);

        assert(
            $saveExistingRecord instanceof SaveExistingRecord &&
            $saveExistingRecord instanceof MockObject
        );

        $saveExistingRecord->expects(self::never())->method(self::anything());

        $transactionManager = $this->createMock(
            DatabaseTransactionManager::class
        );

        assert(
            $transactionManager instanceof DatabaseTransactionManager &&
            $transactionManager instanceof MockObject
        );

        $transactionManager->expects(self::never())->method(self::anything());

        $service = new RestartQueuesByIds(
            $recordQueryFactory,
            $saveExistingRecord,
            $transactionManager,
        );

        $service([]);
    }

    public function testWhenNoRecords(): void
    {
        $ids = ['id1', 'id2'];

        $recordQuery = $this->createMock(RecordQuery::class);

        $recordQuery->expects(self::at(0))
            ->method('withWhere')
            ->with(
                self::equalTo('id'),
                self::equalTo($ids),
                self::equalTo('IN'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(1))
            ->method('withWhere')
            ->with(
                self::equalTo('finished_due_to_error'),
                self::equalTo('1'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(2))
            ->method('all')
            ->willReturn([]);

        assert(
            $recordQuery instanceof RecordQuery &&
            $recordQuery instanceof MockObject
        );

        $recordQueryFactory = $this->createMock(RecordQueryFactory::class);

        assert(
            $recordQueryFactory instanceof RecordQueryFactory &&
            $recordQueryFactory instanceof MockObject
        );

        $recordQueryFactory->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo(new QueueRecord()))
            ->willReturn($recordQuery);

        $saveExistingRecord = $this->createMock(SaveExistingRecord::class);

        assert(
            $saveExistingRecord instanceof SaveExistingRecord &&
            $saveExistingRecord instanceof MockObject
        );

        $saveExistingRecord->expects(self::never())->method(self::anything());

        $transactionManager = $this->createMock(
            DatabaseTransactionManager::class
        );

        assert(
            $transactionManager instanceof DatabaseTransactionManager &&
            $transactionManager instanceof MockObject
        );

        $transactionManager->expects(self::never())->method(self::anything());

        $service = new RestartQueuesByIds(
            $recordQueryFactory,
            $saveExistingRecord,
            $transactionManager,
        );

        $service($ids);
    }

    public function test(): void
    {
        $record1                        = new QueueRecord();
        $record1->id                    = 'id1';
        $record1->is_running            = '1';
        $record1->is_finished           = '1';
        $record1->finished_due_to_error = '1';
        $record1->error_message         = 'test-error-message';
        $record1->finished_at           = 'finished-at-test';

        $record2                        = new QueueRecord();
        $record2->id                    = 'id2';
        $record2->is_running            = '1';
        $record2->is_finished           = '1';
        $record2->finished_due_to_error = '1';
        $record2->error_message         = 'test-error-message';
        $record2->finished_at           = 'finished-at-test';

        $ids = ['id1', 'id2'];

        $recordQuery = $this->createMock(RecordQuery::class);

        $recordQuery->expects(self::at(0))
            ->method('withWhere')
            ->with(
                self::equalTo('id'),
                self::equalTo($ids),
                self::equalTo('IN'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(1))
            ->method('withWhere')
            ->with(
                self::equalTo('finished_due_to_error'),
                self::equalTo('1'),
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(2))
            ->method('all')
            ->willReturn([$record1, $record2]);

        assert(
            $recordQuery instanceof RecordQuery &&
            $recordQuery instanceof MockObject
        );

        $recordQueryFactory = $this->createMock(RecordQueryFactory::class);

        assert(
            $recordQueryFactory instanceof RecordQueryFactory &&
            $recordQueryFactory instanceof MockObject
        );

        $recordQueryFactory->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo(new QueueRecord()))
            ->willReturn($recordQuery);

        $saveExistingRecord = $this->createMock(SaveExistingRecord::class);

        assert(
            $saveExistingRecord instanceof SaveExistingRecord &&
            $saveExistingRecord instanceof MockObject
        );

        $saveExistingRecord->expects(self::at(0))
            ->method('__invoke')
            ->willReturnCallback(
                static function (QueueRecord $record) use ($record1): Payload {
                    self::assertSame($record1, $record);
                    self::assertSame('1', $record->has_started);
                    self::assertSame('0', $record->is_running);
                    self::assertSame('0', $record->is_finished);
                    self::assertSame('0', $record->finished_due_to_error);
                    self::assertSame('', $record->error_message);
                    self::assertNull($record->finished_at);

                    return new Payload(Payload::STATUS_NOT_UPDATED);
                }
            );

        $saveExistingRecord->expects(self::at(1))
            ->method('__invoke')
            ->willReturnCallback(
                static function (QueueRecord $record) use ($record2): Payload {
                    self::assertSame($record2, $record);
                    self::assertSame('1', $record->has_started);
                    self::assertSame('0', $record->is_running);
                    self::assertSame('0', $record->is_finished);
                    self::assertSame('0', $record->finished_due_to_error);
                    self::assertSame('', $record->error_message);
                    self::assertNull($record->finished_at);

                    return new Payload(Payload::STATUS_NOT_UPDATED);
                }
            );

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

        $transactionManager->expects(self::at(1))
            ->method('commit')
            ->willReturn(true);

        $service = new RestartQueuesByIds(
            $recordQueryFactory,
            $saveExistingRecord,
            $transactionManager,
        );

        $service($ids);
    }
}
