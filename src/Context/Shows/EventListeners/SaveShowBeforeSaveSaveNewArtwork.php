<?php

declare(strict_types=1);

namespace App\Context\Shows\EventListeners;

use App\Context\Shows\Events\SaveShowBeforeSave;
use App\Context\Shows\Services\SaveNewArtwork;

class SaveShowBeforeSaveSaveNewArtwork
{
    private SaveNewArtwork $saveNewArtwork;

    public function __construct(SaveNewArtwork $saveNewArtwork)
    {
        $this->saveNewArtwork = $saveNewArtwork;
    }

    public function onBeforeSave(SaveShowBeforeSave $beforeSave): void
    {
        if ($beforeSave->show->newArtworkFileLocation === '') {
            return;
        }

        $this->saveNewArtwork->save($beforeSave->show);
    }
}
