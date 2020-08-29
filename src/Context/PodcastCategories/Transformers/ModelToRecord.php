<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories\Transformers;

use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Persistence\PodcastCategories\PodcastCategoryRecord;

use function array_map;
use function Safe\json_encode;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class ModelToRecord
{
    public function transform(PodcastCategoryModel $model): PodcastCategoryRecord
    {
        $record = new PodcastCategoryRecord();

        $record->id = $model->id;

        $record->parent_id = $model->parent->id;

        $parentChain = array_map(
            static fn (PodcastCategoryModel $m) => $m->id,
            $model->parentChain,
        );

        $record->parent_chain = json_encode($parentChain);

        $record->name = $model->name;

        return $record;
    }
}
