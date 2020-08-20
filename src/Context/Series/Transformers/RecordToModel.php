<?php

declare(strict_types=1);

namespace App\Context\Series\Transformers;

use App\Context\Series\Models\SeriesModel;
use App\Context\Shows\Models\ShowModel;
use App\Persistence\Series\SeriesRecord;

class RecordToModel
{
    public function transform(
        SeriesRecord $record,
        ShowModel $showModel
    ): SeriesModel {
        $model = new SeriesModel();

        $model->id = $record->id;

        $model->title = $record->title;

        $model->slug = $record->slug;

        $model->description = $record->description;

        $model->show = $showModel;

        return $model;
    }
}
