<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Services;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadTrackerModel;
use App\Context\EpisodeDownloadStats\Services\Internal\SaveTrackerExisting;
use App\Context\EpisodeDownloadStats\Services\Internal\SaveTrackerNew;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Throwable;

class SaveTracker
{
    private DatabaseTransactionManager $transactionManager;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private SaveTrackerNew $saveNew;
    private SaveTrackerExisting $saveExisting;

    public function __construct(
        DatabaseTransactionManager $transactionManager,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        SaveTrackerNew $saveNew,
        SaveTrackerExisting $saveExisting
    ) {
        $this->transactionManager = $transactionManager;
        $this->uuidFactory        = $uuidFactory;
        $this->saveNew            = $saveNew;
        $this->saveExisting       = $saveExisting;
    }

    public function save(EpisodeDownloadTrackerModel $model): Payload
    {
        try {
            return $this->innerSave($model);
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();

            return new Payload(Payload::STATUS_ERROR);
        }
    }

    /**
     * @throws Throwable
     */
    private function innerSave(EpisodeDownloadTrackerModel $model): Payload
    {
        $this->transactionManager->beginTransaction();

        $isNew = false;

        if ($model->id === '') {
            $model->id = $this->uuidFactory->uuid1()->toString();

            $isNew = true;
        }

        if ($isNew) {
            $payload = $this->saveNew->save($model);
        } else {
            $payload = $this->saveExisting->save($model);
        }

        $this->transactionManager->commit();

        return $payload;
    }
}
