<?php

declare(strict_types=1);

namespace App\Context\Shows\EventListeners;

use App\Context\Shows\Events\SaveShowBeforeSave;
use App\Context\Shows\Services\SaveShowKeywords;

class SaveShowBeforeSaveSaveShowKeywords
{
    private SaveShowKeywords $saveShowKeywords;

    public function __construct(SaveShowKeywords $saveShowKeywords)
    {
        $this->saveShowKeywords = $saveShowKeywords;
    }

    public function onBeforeSave(SaveShowBeforeSave $beforeSave): void
    {
        $this->saveShowKeywords->save($beforeSave->show);
    }
}
