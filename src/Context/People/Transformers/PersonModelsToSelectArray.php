<?php

declare(strict_types=1);

namespace App\Context\People\Transformers;

use App\Context\People\Models\PersonModel;

class PersonModelsToSelectArray
{
    /**
     * @param PersonModel[] $models
     *
     * @return mixed[]
     */
    public function transform(array $models): array
    {
        $returnArray = [];

        foreach ($models as $model) {
            $returnArray[] = [
                'value' => $model->id,
                'label' => $model->getFullName(),
            ];
        }

        return $returnArray;
    }
}
