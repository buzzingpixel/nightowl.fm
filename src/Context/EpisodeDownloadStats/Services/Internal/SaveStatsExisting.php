<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Services\Internal;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadStatsModel;
use App\Context\EpisodeDownloadStats\Transformers\StatsModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use Exception;

class SaveStatsExisting
{
    private SaveExistingRecord $saveRecord;
    private StatsModelToRecord $modelToRecord;

    public function __construct(
        SaveExistingRecord $saveRecord,
        StatsModelToRecord $modelToRecord
    ) {
        $this->saveRecord    = $saveRecord;
        $this->modelToRecord = $modelToRecord;
    }

    /**
     * @throws Exception
     */
    public function save(EpisodeDownloadStatsModel $model): Payload
    {
        $payload = $this->saveRecord->save(
            $this->modelToRecord->transform($model)
        );

        if ($payload->getStatus() === Payload::STATUS_UPDATED) {
            return $payload;
        }

        throw new Exception('Unknown error saving model');
    }
}
