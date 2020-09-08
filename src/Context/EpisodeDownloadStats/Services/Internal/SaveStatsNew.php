<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Services\Internal;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadStatsModel;
use App\Context\EpisodeDownloadStats\Transformers\StatsModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveNewRecord;
use Exception;

class SaveStatsNew
{
    private SaveNewRecord $saveRecord;
    private StatsModelToRecord $modelToRecord;

    public function __construct(
        SaveNewRecord $saveRecord,
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

        if ($payload->getStatus() === Payload::STATUS_CREATED) {
            return $payload;
        }

        throw new Exception('Unknown error saving model');
    }
}
