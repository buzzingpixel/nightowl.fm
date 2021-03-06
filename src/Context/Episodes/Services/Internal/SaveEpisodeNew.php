<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Transformers\ModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveNewRecord;
use Exception;

class SaveEpisodeNew
{
    private SaveNewRecord $saveRecord;
    private ModelToRecord $modelToRecord;

    public function __construct(
        SaveNewRecord $saveRecord,
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

        if ($payload->getStatus() === Payload::STATUS_CREATED) {
            return $payload;
        }

        throw new Exception('Unknown error saving episode');
    }
}
