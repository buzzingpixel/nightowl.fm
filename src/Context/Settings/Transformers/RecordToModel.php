<?php

declare(strict_types=1);

namespace App\Context\Settings\Transformers;

use App\Context\Settings\Models\SettingModel;
use App\Persistence\Settings\SettingRecord;

use function Safe\json_decode;

class RecordToModel
{
    public function __invoke(SettingRecord $record): SettingModel
    {
        return $this->transform($record);
    }

    public function transform(SettingRecord $record): SettingModel
    {
        $model = new SettingModel();

        $model->id = $record->id;

        $model->key = $record->key;

        /** @noinspection PhpUnhandledExceptionInspection */
        $model->value = (array) json_decode($record->value, true);

        return $model;
    }
}
