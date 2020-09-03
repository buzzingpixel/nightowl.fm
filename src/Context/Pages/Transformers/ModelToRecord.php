<?php

declare(strict_types=1);

namespace App\Context\Pages\Transformers;

use App\Context\Pages\Models\PageModel;
use App\Persistence\Pages\PageRecord;

class ModelToRecord
{
    public function transform(PageModel $model): PageRecord
    {
        $record = new PageRecord();

        $record->id = $model->id;

        $record->title = $model->title;

        $record->uri = $model->uri;

        $record->content = $model->content;

        return $record;
    }
}
