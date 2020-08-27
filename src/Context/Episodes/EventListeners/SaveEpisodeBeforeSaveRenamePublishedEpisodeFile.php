<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\SaveEpisodeBeforeSave;
use App\Context\Episodes\Services\Internal\RenamePublishedEpisodeFile;

class SaveEpisodeBeforeSaveRenamePublishedEpisodeFile
{
    private RenamePublishedEpisodeFile $renamePublishedEpisodeFile;

    public function __construct(
        RenamePublishedEpisodeFile $renamePublishedEpisodeFile
    ) {
        $this->renamePublishedEpisodeFile = $renamePublishedEpisodeFile;
    }

    public function onBeforeSave(SaveEpisodeBeforeSave $beforeSave): void
    {
        $this->renamePublishedEpisodeFile->rename(
            $beforeSave->episode
        );
    }
}
