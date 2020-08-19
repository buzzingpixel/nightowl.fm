<?php

declare(strict_types=1);

namespace App\Context\Series\Transformers;

use App\Context\Series\Models\SeriesModel;
use App\Persistence\Series\SeriesRecord;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class ModelToRecord
{
    public function transform(SeriesModel $model): SeriesRecord
    {
        $record = new SeriesRecord();

        $record->id = $model->id;

        $record->show_id = $model->show->id;

        $record->title = $model->title;

        $record->slug = $model->slug;

        $record->description = $model->description;

        return $record;
    }
}
