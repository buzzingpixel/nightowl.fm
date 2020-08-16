<?php

declare(strict_types=1);

namespace App\Context\Shows\Services\Internal;

use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\Transformers\ModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveNewRecord;
use Exception;

class SaveShowNew
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
    public function save(ShowModel $show): Payload
    {
        $payload = $this->saveRecord->save(
            $this->modelToRecord->transform($show)
        );

        if ($payload->getStatus() === Payload::STATUS_CREATED) {
            return $payload;
        }

        throw new Exception('Unknown error saving  show');
    }
}
