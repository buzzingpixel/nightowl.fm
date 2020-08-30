<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories\Transformers;

use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Persistence\PodcastCategories\PodcastCategoryRecord;

use function array_values;
use function assert;
use function count;
use function end;
use function Safe\ksort;

use const SORT_NATURAL;

class RecordToModel
{
    /**
     * @param PodcastCategoryModel[] $parentChain
     */
    public function transform(
        PodcastCategoryRecord $record,
        array $parentChain = []
    ): PodcastCategoryModel {
        // Validate all items in the array are the correct type
        foreach ($parentChain as $item) {
            assert($item instanceof PodcastCategoryModel);
        }

        $model = new PodcastCategoryModel($parentChain);

        $model->id = $record->id;

        $model->name = $record->name;

        if (count($parentChain) > 0) {
            $parent = end($parentChain);

            if ($parent instanceof PodcastCategoryModel) {
                $parentChildren = [];

                foreach ($parent->children as $child) {
                    $parentChildren[$child->name] = $child;
                }

                $parentChildren[$model->name] = $model;

                ksort($parentChildren, SORT_NATURAL);

                /** @psalm-suppress MixedPropertyTypeCoercion */
                $parent->children = array_values($parentChildren);
            }
        }

        return $model;
    }
}
