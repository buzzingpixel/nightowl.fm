<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories\Services\Internal;

use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Context\PodcastCategories\Transformers\ModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use Exception;

class SavePodcastCategoryExisting
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
    public function save(PodcastCategoryModel $model): Payload
    {
        $payload = $this->saveRecord->save(
            $this->modelToRecord->transform($model)
        );

        if ($payload->getStatus() === Payload::STATUS_UPDATED) {
            return $payload;
        }

        throw new Exception('Unknown error saving podcast category');
    }
}
