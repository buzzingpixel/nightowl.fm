<?php

declare(strict_types=1);

namespace App\Context\Pages\Services;

use App\Context\Pages\Events\DeletePageAfterDelete;
use App\Context\Pages\Events\DeletePageBeforeDelete;
use App\Context\Pages\Models\PageModel;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\Pages\PageRecord;
use Exception;
use PDO;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class DeletePage
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

    public function delete(PageModel $page): Payload
    {
        try {
            return $this->innerRun($page);
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            return new Payload(
                Payload::STATUS_ERROR,
                [
                    'message' => 'Unable to delete ' . $page->title,
                ]
            );
        }
    }

    /**
     * @throws Exception
     */
    private function innerRun(PageModel $page): Payload
    {
        $this->transactionManager->beginTransaction();

        $beforeEvent = new DeletePageBeforeDelete($page);

        $this->eventDispatcher->dispatch($beforeEvent);

        if (! $beforeEvent->isValid) {
            throw new Exception();
        }

        $statement = $this->pdo->prepare(
            'DELETE FROM ' .
            PageRecord::tableName() .
            ' WHERE id=:id'
        );

        if (! $statement->execute([':id' => $page->id])) {
            throw new Exception();
        }

        $afterEvent = new DeletePageAfterDelete($page);

        $this->eventDispatcher->dispatch($afterEvent);

        if (! $afterEvent->isValid) {
            throw new Exception();
        }

        $this->transactionManager->commit();

        return new Payload(Payload::STATUS_DELETED);
    }
}
