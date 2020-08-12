<?php

declare(strict_types=1);

namespace App\Context\People\Services;

use App\Context\People\Events\DeletePersonAfterDelete;
use App\Context\People\Events\DeletePersonBeforeDelete;
use App\Context\People\Models\PersonModel;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\People\PersonRecord;
use Exception;
use PDO;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class DeletePerson
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

    public function delete(PersonModel $person): Payload
    {
        try {
            return $this->innerRun($person);
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            return new Payload(
                Payload::STATUS_ERROR,
                [
                    'message' => 'Unable to delete ' . $person->getFullName(),
                ]
            );
        }
    }

    /**
     * @throws Exception
     */
    private function innerRun(PersonModel $person): Payload
    {
        $this->transactionManager->beginTransaction();

        $beforeEvent = new DeletePersonBeforeDelete($person);

        $this->eventDispatcher->dispatch($person);

        if (! $beforeEvent->isValid) {
            throw new Exception();
        }

        $statement = $this->pdo->prepare(
            'DELETE FROM ' .
            PersonRecord::tableName() .
            ' WHERE id=:id'
        );

        if (
            ! $statement->execute(
                [':id' => $person->id]
            )
        ) {
            throw new Exception();
        }

        $afterEvent = new DeletePersonAfterDelete($person);

        $this->eventDispatcher->dispatch($afterEvent);

        if (! $afterEvent->isValid) {
            throw new Exception();
        }

        $this->transactionManager->commit();

        return new Payload(Payload::STATUS_DELETED);
    }
}
