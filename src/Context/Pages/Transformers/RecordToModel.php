<?php

declare(strict_types=1);

namespace App\Context\Pages\Transformers;

use App\Context\Pages\Models\PageModel;
use App\Persistence\Pages\PageRecord;

class RecordToModel
{
    public function transform(PageRecord $record): PageModel
    {
        $model = new PageModel();

        $model->id = $record->id;

        $model->title = $record->title;

        $model->uri = $record->uri;

        $model->content = $record->content;

        return $model;
    }
}
