<?php

declare(strict_types=1);

namespace App\Context\Series\Transformers;

use App\Context\Series\Models\SeriesModel;

use function array_map;

class SeriesModelsToSelectArray
{
    /**
     * @param SeriesModel[] $models
     *
     * @return mixed[]
     */
    public function transform(array $models): array
    {
        return array_map(
            static fn (SeriesModel $model) => [
                'value' => $model->id,
                'label' => $model->title,
            ],
            $models
        );
    }
}
