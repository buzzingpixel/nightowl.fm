<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories\Transformers;

use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Persistence\PodcastCategories\PodcastCategoryRecord;

use function array_walk;

class RecordToModel
{
    /**
     * @param PodcastCategoryModel[] $parentChain
     */
    public function transform(
        PodcastCategoryRecord $record,
        array $parentChain
    ): PodcastCategoryModel {
        // Validate all items in the array are the correct type because of the
        // typecast in the function
        array_walk(
            $parentChain,
            static fn (PodcastCategoryModel $m) => $m->id,
        );

        $model = new PodcastCategoryModel($parentChain);

        $model->id = $record->id;

        $model->name = $record->name;

        return $model;
    }
}
