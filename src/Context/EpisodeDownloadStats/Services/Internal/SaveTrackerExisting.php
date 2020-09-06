<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Services\Internal;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadTrackerModel;
use App\Context\EpisodeDownloadStats\Transformers\TrackerModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use Exception;

class SaveTrackerExisting
{
    private SaveExistingRecord $saveRecord;
    private TrackerModelToRecord $modelToRecord;

    public function __construct(
        SaveExistingRecord $saveRecord,
        TrackerModelToRecord $modelToRecord
    ) {
        $this->saveRecord    = $saveRecord;
        $this->modelToRecord = $modelToRecord;
    }

    /**
     * @throws Exception
     */
    public function save(EpisodeDownloadTrackerModel $model): Payload
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
