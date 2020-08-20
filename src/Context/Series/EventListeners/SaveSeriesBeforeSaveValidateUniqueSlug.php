<?php

declare(strict_types=1);

namespace App\Context\Series\EventListeners;

use App\Context\Series\Events\SaveSeriesBeforeSave;
use App\Context\Series\Services\ValidateUniqueSeriesSlug;

class SaveSeriesBeforeSaveValidateUniqueSlug
{
    private ValidateUniqueSeriesSlug $validator;

    public function __construct(ValidateUniqueSeriesSlug $validator)
    {
        $this->validator = $validator;
    }

    public function onBeforeSave(SaveSeriesBeforeSave $beforeSave): void
    {
        $beforeSave->isValid = $this->validator->validate(
            $beforeSave->series->slug,
            $beforeSave->series->show->id,
            $beforeSave->series->id,
        );

        if ($beforeSave->isValid) {
            return;
        }

        $beforeSave->stopPropagation();
    }
}
