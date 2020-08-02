<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Services;

use App\Context\Queue\Services\DeleteQueuesByIds;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\Queue\QueueItemRecord;
use App\Persistence\Queue\QueueRecord;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_fill;
use function assert;
use function count;
use function implode;

class DeleteQueuesByIdsTest extends TestCase
{
    public function testWhenNoIds(): void
    {
        $pdo = $this->createMock(PDO::class);

        assert(
            $pdo instanceof PDO &&
            $pdo instanceof MockObject
        );

        $pdo->expects(self::never())
            ->method(self::anything());

        $transactionManager = $this->createMock(
            DatabaseTransactionManager::class
        );

        assert(
            $transactionManager instanceof DatabaseTransactionManager &&
            $transactionManager instanceof MockObject
        );

        $transactionManager->expects(self::never())
            ->method(self::anything());

        $service = new DeleteQueuesByIds($pdo, $transactionManager);

        $service([]);
    }

    public function test(): void
    {
        $ids = ['id1', 'id2'];

        $in = implode(',', array_fill(0, count($ids), '?'));

        $queueStatement = $this->createMock(PDOStatement::class);

        assert(
            $queueStatement instanceof PDOStatement &&
            $queueStatement instanceof MockObject
        );

        $queueStatement->expects(self::once())
            ->method('execute')
            ->with(self::equalTo($ids))
            ->willReturn(true);

        $itemsStatement = $this->createMock(PDOStatement::class);

        assert(
            $itemsStatement instanceof PDOStatement &&
            $itemsStatement instanceof MockObject
        );

        $itemsStatement->expects(self::once())
            ->method('execute')
            ->with(self::equalTo($ids))
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);

        assert(
            $pdo instanceof PDO &&
            $pdo instanceof MockObject
        );

        $pdo->expects(self::at(0))
            ->method('prepare')
            ->with(self::equalTo(
                'DELETE FROM ' . (new QueueRecord())->getTableName() .
                ' WHERE id IN (' . $in . ')',
            ))
            ->willReturn($queueStatement);

        $pdo->expects(self::at(1))
            ->method('prepare')
            ->with(self::equalTo(
                'DELETE FROM ' . (new QueueItemRecord())->getTableName() .
                ' WHERE queue_id IN (' . $in . ')',
            ))
            ->willReturn($itemsStatement);

        $transactionManager = $this->createMock(
            DatabaseTransactionManager::class
        );

        assert(
            $transactionManager instanceof DatabaseTransactionManager &&
            $transactionManager instanceof MockObject
        );

        $transactionManager->expects(self::at(0))
            ->method('beginTransaction');

        $transactionManager->expects(self::at(1))
            ->method('commit');

        $service = new DeleteQueuesByIds($pdo, $transactionManager);

        $service($ids);
    }
}
