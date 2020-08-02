<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\Transformers\TransformQueueModelToRecord;
use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use DateTimeZone;
use Exception;
use Safe\DateTimeImmutable;
use Throwable;

class MarkItemAsStarted
{
    private TransformQueueModelToRecord $queueModelToRecord;
    private SaveExistingRecord $saveExistingRecord;

    public function __construct(
        TransformQueueModelToRecord $queueModelToRecord,
        SaveExistingRecord $saveExistingRecord
    ) {
        $this->queueModelToRecord = $queueModelToRecord;
        $this->saveExistingRecord = $saveExistingRecord;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(QueueModel $model): void
    {
        $model->hasStarted = true;

        $model->isRunning = true;

        $diff = $model->addedAt->diff($model->initialAssumeDeadAfter);

        /** @noinspection PhpUnhandledExceptionInspection */
        $newAssumeDeadAfter = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        $newAssumeDeadAfter = $newAssumeDeadAfter->add($diff);

        $model->assumeDeadAfter = $newAssumeDeadAfter;

        $record = ($this->queueModelToRecord)($model);

        $payload = ($this->saveExistingRecord)($record);

        if ($payload->getStatus() === Payload::STATUS_UPDATED) {
            return;
        }

        throw new Exception('An unknown error occurred');
    }
}
