<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Persistence\DatabaseTransactionManager;
use App\Persistence\Queue\QueueItemRecord;
use App\Persistence\Queue\QueueRecord;
use PDO;

use function array_fill;
use function count;
use function implode;

class DeleteQueuesByIds
{
    private PDO $pdo;
    private DatabaseTransactionManager $transactionManager;

    public function __construct(
        PDO $pdo,
        DatabaseTransactionManager $transactionManager
    ) {
        $this->pdo                = $pdo;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param string[] $ids
     */
    public function __invoke(array $ids): void
    {
        if (count($ids) < 1) {
            return;
        }

        $in = implode(
            ',',
            array_fill(0, count($ids), '?')
        );

        $this->transactionManager->beginTransaction();

        $queueStatement = $this->pdo->prepare(
            'DELETE FROM ' . (new QueueRecord())->getTableName() .
            ' WHERE id IN (' . $in . ')',
        );

        $queueStatement->execute($ids);

        $itemsStatement = $this->pdo->prepare(
            'DELETE FROM ' . (new QueueItemRecord())->getTableName() .
            ' WHERE queue_id IN (' . $in . ')',
        );

        $itemsStatement->execute($ids);

        $this->transactionManager->commit();
    }
}
