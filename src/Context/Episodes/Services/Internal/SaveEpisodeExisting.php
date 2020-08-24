<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Transformers\ModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use Exception;

class SaveEpisodeExisting
{
    private SaveExistingRecord $saveRecord;
    private ModelToRecord $modelToRecord;

    public function __construct(
        SaveExistingRecord $saveRecord,
        ModelToRecord $modelToRecord
    ) {
        $this->saveRecord    = $saveRecord;
        $this->modelToRecord = $modelToRecord;
    }

    /**
     * @throws Exception
     */
    public function save(EpisodeModel $episode): Payload
    {
        $payload = $this->saveRecord->save(
            $this->modelToRecord->transform($episode)
        );

        if ($payload->getStatus() === Payload::STATUS_UPDATED) {
            return $payload;
        }

        throw new Exception('Unknown error saving episode');
    }
}
