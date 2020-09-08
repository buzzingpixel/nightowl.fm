<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Services;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadStatsModel;
use App\Context\EpisodeDownloadStats\Services\Internal\SaveStatsExisting;
use App\Context\EpisodeDownloadStats\Services\Internal\SaveStatsNew;
use App\Payload\Payload;
use App\Persistence\DatabaseTransactionManager;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Throwable;

class SaveStats
{
    private DatabaseTransactionManager $transactionManager;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private SaveStatsNew $saveNew;
    private SaveStatsExisting $saveExisting;

    public function __construct(
        DatabaseTransactionManager $transactionManager,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        SaveStatsNew $saveNew,
        SaveStatsExisting $saveExisting
    ) {
        $this->transactionManager = $transactionManager;
        $this->uuidFactory        = $uuidFactory;
        $this->saveNew            = $saveNew;
        $this->saveExisting       = $saveExisting;
    }

    public function save(EpisodeDownloadStatsModel $model): Payload
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
    private function innerSave(EpisodeDownloadStatsModel $model): Payload
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
