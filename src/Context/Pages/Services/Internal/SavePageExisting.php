<?php

declare(strict_types=1);

namespace App\Context\Pages\Services\Internal;

use App\Context\Pages\Models\PageModel;
use App\Context\Pages\Transformers\ModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use Exception;

class SavePageExisting
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
    public function save(PageModel $page): Payload
    {
        $payload = $this->saveRecord->save(
            $this->modelToRecord->transform($page)
        );

        if ($payload->getStatus() === Payload::STATUS_UPDATED) {
            return $payload;
        }

        throw new Exception('Unknown error saving page');
    }
}
