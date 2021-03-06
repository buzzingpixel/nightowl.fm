<?php

declare(strict_types=1);

namespace App\Context\People\Transformers;

use App\Context\People\Models\PersonModel;

use function array_map;

class PersonModelsToSelectArray
{
    /**
     * @param PersonModel[] $models
     *
     * @return mixed[]
     */
    public function transform(array $models): array
    {
        return array_map(
            static fn (PersonModel $model) => [
                'value' => $model->id,
                'label' => $model->getFullName(),
            ],
            $models
        );
    }
}
