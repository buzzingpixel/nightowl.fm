<?php

declare(strict_types=1);

namespace App\Context\Settings\Transformers;

use App\Context\Settings\Models\SettingModel;
use App\Persistence\Settings\SettingRecord;

use function Safe\json_encode;

class ModelToRecord
{
    public function transform(SettingModel $model): SettingRecord
    {
        $record = new SettingRecord();

        $record->id = $model->id;

        $record->key = $model->key;

        /** @noinspection PhpUnhandledExceptionInspection */
        $record->value = json_encode($model->value);

        return $record;
    }
}
