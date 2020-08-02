<?php

declare(strict_types=1);

namespace App\Context\Queue\Transformers;

use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Persistence\Constants;
use App\Persistence\Queue\QueueItemRecord;
use Safe\DateTimeImmutable;
use Throwable;

use function in_array;
use function is_array;
use function Safe\json_decode;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class QueueItemRecordToModel
{
    public function __invoke(
        QueueItemRecord $record,
        QueueModel $queueModel
    ): QueueItemModel {
        $model = new QueueItemModel();

        $model->id = $record->id;

        $queueModel->addItem($model);

        $model->runOrder = (int) $record->run_order;

        $model->isFinished = in_array(
            $record->is_finished,
            ['1', 1, 'true', true],
            true,
        );

        try {
            /** @psalm-suppress PossiblyNullArgument */
            $finishedAt = DateTimeImmutable::createFromFormat(
                Constants::POSTGRES_OUTPUT_FORMAT,
                (string) $record->finished_at,
            );

            $model->finishedAt = $finishedAt;
        } catch (Throwable $e) {
            $model->finishedAt = null;
        }

        $model->class = $record->class;

        $model->method = $record->method;

        try {
            /** @psalm-suppress MixedAssignment */
            $context = json_decode((string) $record->context, true);
        } catch (Throwable $e) {
            $context = null;
        }

        if (is_array($context)) {
            $model->context = $context;
        }

        return $model;
    }
}
