<?php

declare(strict_types=1);

namespace App\Context\Series\Services\Internal;

use App\Context\Series\Models\SeriesModel;
use App\Context\Series\Transformers\ModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use Exception;

class SaveExistingSeries
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
    public function save(SeriesModel $series): Payload
    {
        $payload = $this->saveRecord->save(
            $this->modelToRecord->transform($series)
        );

        if ($payload->getStatus() === Payload::STATUS_UPDATED) {
            return $payload;
        }

        throw new Exception('Unknown error saving show');
    }
}
