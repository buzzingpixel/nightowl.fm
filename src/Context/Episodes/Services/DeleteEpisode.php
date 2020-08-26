<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services;

use App\Context\Episodes\Events\DeleteEpisodeAfterDelete;
use App\Context\Episodes\Events\DeleteEpisodeBeforeDelete;
use App\Context\Episodes\Models\EpisodeModel;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\Episodes\EpisodeRecord;
use Exception;
use PDO;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class DeleteEpisode
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

    public function delete(EpisodeModel $episode): Payload
    {
        try {
            return $this->innerRun($episode);
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            return new Payload(
                Payload::STATUS_ERROR,
                ['message' => 'Unable to delete episode']
            );
        }
    }

    /**
     * @throws Exception
     */
    private function innerRun(EpisodeModel $episode): Payload
    {
        $this->transactionManager->beginTransaction();

        $beforeEvent = new DeleteEpisodeBeforeDelete($episode);

        $this->eventDispatcher->dispatch($beforeEvent);

        if (! $beforeEvent->isValid) {
            throw new Exception();
        }

        $statement = $this->pdo->prepare(
            'DELETE FROM ' .
            EpisodeRecord::tableName() .
            ' WHERE id=:id'
        );

        if (! $statement->execute([':id' => $episode->id])) {
            throw new Exception();
        }

        $afterEvent = new DeleteEpisodeAfterDelete($episode);

        $this->eventDispatcher->dispatch($afterEvent);

        if (! $afterEvent->isValid) {
            throw new Exception();
        }

        $this->transactionManager->commit();

        return new Payload(Payload::STATUS_DELETED);
    }
}
