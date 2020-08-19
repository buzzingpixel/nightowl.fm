<?php

declare(strict_types=1);

namespace App\Context\Shows\Services;

use App\Context\Shows\Events\DeleteShowAfterDelete;
use App\Context\Shows\Events\DeleteShowBeforeDelete;
use App\Context\Shows\Models\ShowModel;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\Shows\ShowRecord;
use Exception;
use PDO;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class DeleteShow
{
    private EventDispatcherInterface $eventDispatcher;
    private DatabaseTransactionManager $transactionManager;
    private PDO $pdo;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DatabaseTransactionManager $transactionManager,
        PDO $pdo
    ) {
        $this->eventDispatcher    = $eventDispatcher;
        $this->transactionManager = $transactionManager;
        $this->pdo                = $pdo;
    }

    public function delete(ShowModel $show): Payload
    {
        try {
            return $this->innerRun($show);
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            return new Payload(
                Payload::STATUS_ERROR,
                [
                    'message' => 'Unable to delete ' . $show->title,
                ]
            );
        }
    }

    /**
     * @throws Exception
     */
    private function innerRun(ShowModel $show): Payload
    {
        $this->transactionManager->beginTransaction();

        $beforeEvent = new DeleteShowBeforeDelete($show);

        $this->eventDispatcher->dispatch($beforeEvent);

        if (! $beforeEvent->isValid) {
            throw new Exception();
        }

        $statement = $this->pdo->prepare(
            'DELETE FROM ' .
            ShowRecord::tableName() .
            ' WHERE id=:id'
        );

        if (! $statement->execute([':id' => $show->id])) {
            throw new Exception();
        }

        $afterEvent = new DeleteShowAfterDelete($show);

        $this->eventDispatcher->dispatch($afterEvent);

        if (! $afterEvent->isValid) {
            throw new Exception();
        }

        $this->transactionManager->commit();

        return new Payload(Payload::STATUS_DELETED);
    }
}
