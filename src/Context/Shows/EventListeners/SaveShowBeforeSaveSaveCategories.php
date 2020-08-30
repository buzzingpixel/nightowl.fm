<?php

declare(strict_types=1);

namespace App\Context\Shows\EventListeners;

use App\Context\Shows\Events\SaveShowBeforeSave;
use App\Context\Shows\Services\Internal\SaveShowPodcastCategories;

class SaveShowBeforeSaveSaveCategories
{
    private SaveShowPodcastCategories $saveShowCategories;

    public function __construct(SaveShowPodcastCategories $saveShowCategories)
    {
        $this->saveShowCategories = $saveShowCategories;
    }

    public function onBeforeSave(SaveShowBeforeSave $beforeSave): void
    {
        $this->saveShowCategories->save($beforeSave->show);
    }
}
