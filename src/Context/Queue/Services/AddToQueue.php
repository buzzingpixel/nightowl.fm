<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\Transformers\TransformQueueItemtoRecord;
use App\Context\Queue\Transformers\TransformQueueModelToRecord;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\SaveNewRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Exception;
use Throwable;

use function array_walk;

class AddToQueue
{
    private DatabaseTransactionManager $transactionManager;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private SaveNewRecord $saveNewRecord;
    private TransformQueueModelToRecord $queueToRecord;
    private TransformQueueItemtoRecord $queueItemToRecord;

    public function __construct(
        DatabaseTransactionManager $transactionManager,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        SaveNewRecord $saveNewRecord,
        TransformQueueModelToRecord $queueToRecord,
        TransformQueueItemtoRecord $queueItemToRecord
    ) {
        $this->transactionManager = $transactionManager;
        $this->uuidFactory        = $uuidFactory;
        $this->saveNewRecord      = $saveNewRecord;
        $this->queueToRecord      = $queueToRecord;
        $this->queueItemToRecord  = $queueItemToRecord;
    }

    public function __invoke(QueueModel $queueModel): Payload
    {
        try {
            $this->transactionManager->beginTransaction();

            $queueModel->id = $this->uuidFactory->uuid1()->toString();

            $queueRecord = ($this->queueToRecord)($queueModel);

            $payload = ($this->saveNewRecord)($queueRecord);

            if ($payload->getStatus() !== Payload::STATUS_CREATED) {
                throw new Exception();
            }

            $items = $queueModel->items;

            array_walk($items, [
                $this,
                'saveItem',
            ]);

            $this->transactionManager->commit();

            return new Payload(Payload::STATUS_SUCCESSFUL);
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            return new Payload(
                Payload::STATUS_ERROR,
                ['message' => 'An unknown error occurred']
            );
        }
    }

    protected function saveItem(QueueItemModel $queueItem, int $index): void
    {
        $count = $index + 1;

        $queueItem->id = $this->uuidFactory->uuid1()->toString();

        $queueItem->runOrder = $count;

        $record = ($this->queueItemToRecord)($queueItem);

        $payload = ($this->saveNewRecord)($record);

        if ($payload->getStatus() !== Payload::STATUS_CREATED) {
            throw new Exception();
        }
    }
}
