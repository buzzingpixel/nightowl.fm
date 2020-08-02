<?php

declare(strict_types=1);

namespace App\Context\Schedule\Services;

use App\Context\Schedule\Models\ScheduleItemModel;
use App\Context\Schedule\Transformers\TransformModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use App\Persistence\SaveNewRecord;
use App\Persistence\Schedule\ScheduleTrackingRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use Exception;
use Throwable;

class SaveSchedule
{
    private TransformModelToRecord $transformModelToRecord;
    private SaveNewRecord $saveNewRecord;
    private SaveExistingRecord $saveExistingRecord;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;

    public function __construct(
        TransformModelToRecord $transformModelToRecord,
        SaveNewRecord $saveNewRecord,
        SaveExistingRecord $saveExistingRecord,
        UuidFactoryWithOrderedTimeCodec $uuidFactory
    ) {
        $this->transformModelToRecord = $transformModelToRecord;
        $this->saveNewRecord          = $saveNewRecord;
        $this->saveExistingRecord     = $saveExistingRecord;
        $this->uuidFactory            = $uuidFactory;
    }

    public function __invoke(ScheduleItemModel $model): Payload
    {
        if ($model->class === '') {
            return new Payload(
                Payload::STATUS_NOT_VALID,
                ['message' => 'Class is required']
            );
        }

        try {
            $record = ($this->transformModelToRecord)($model);

            if (! $model->id) {
                return $this->saveNewRecord($record, $model);
            }

            return ($this->saveExistingRecord)($record);
        } catch (Throwable $e) {
            return new Payload(
                Payload::STATUS_ERROR,
                ['message' => 'An unknown error occurred']
            );
        }
    }

    /**
     * @throws Throwable
     */
    private function saveNewRecord(
        ScheduleTrackingRecord $record,
        ScheduleItemModel $model
    ): Payload {
        $uid = $this->uuidFactory->uuid1()->toString();

        $record->id = $uid;

        $payload = ($this->saveNewRecord)($record);

        if ($payload->getStatus() !== Payload::STATUS_CREATED) {
            throw new Exception();
        }

        $model->id = $record->id;

        return $payload;
    }
}
