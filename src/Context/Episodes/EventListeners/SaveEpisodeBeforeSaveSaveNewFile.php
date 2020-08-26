<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\SaveEpisodeBeforeSave;
use App\Context\Episodes\Services\Internal\SaveNewFile;

class SaveEpisodeBeforeSaveSaveNewFile
{
    private SaveNewFile $saveNewFile;

    public function __construct(SaveNewFile $saveNewFile)
    {
        $this->saveNewFile = $saveNewFile;
    }

    public function onBeforeSave(SaveEpisodeBeforeSave $beforeSave): void
    {
        if ($beforeSave->episode->newFileLocation === '') {
            return;
        }

        $this->saveNewFile->save($beforeSave->episode);
    }
}
