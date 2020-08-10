<?php

declare(strict_types=1);

namespace App\Context\People\Services\Internal;

use App\Context\People\Models\PersonModel;
use App\Context\People\Transformers\ModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveNewRecord;
use Exception;

class SavePersonNew
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
    public function save(PersonModel $person): Payload
    {
        $payload = $this->saveRecord->save(
            $this->modelToRecord->transform($person)
        );

        if ($payload->getStatus() === Payload::STATUS_CREATED) {
            return $payload;
        }

        throw new Exception('Unknown error saving  person');
    }
}
