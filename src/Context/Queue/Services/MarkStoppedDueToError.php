<?php

declare(strict_types=1);

namespace App\Context\Queue\Services;

use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\Transformers\TransformQueueModelToRecord;
use App\Persistence\SaveExistingRecord;
use DateTimeZone;
use Safe\DateTimeImmutable;
use Throwable;

use function get_class;

use const PHP_EOL;

class MarkStoppedDueToError
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

    public function __invoke(
        QueueModel $model,
        ?Throwable $e = null
    ): void {
        $msg = '';

        if ($e !== null) {
            $eol  = PHP_EOL . PHP_EOL;
            $msg .= 'Exception Type: ' . get_class($e) . $eol;
            $msg .= 'Error Code: ' . $e->getCode() . $eol;
            $msg .= 'File: ' . $e->getFile() . $eol;
            $msg .= 'Line: ' . $e->getLine() . $eol;
            $msg .= 'Message: ' . $e->getMessage() . $eol;
            $msg .= 'Trace . ' . $e->getTraceAsString();
        }

        $model->isRunning          = false;
        $model->isFinished         = true;
        $model->finishedDueToError = true;
        $model->errorMessage       = $msg;
        $model->finishedAt         = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC'),
        );

        $record = ($this->queueModelToRecord)($model);

        ($this->saveExistingRecord)($record);
    }
}
