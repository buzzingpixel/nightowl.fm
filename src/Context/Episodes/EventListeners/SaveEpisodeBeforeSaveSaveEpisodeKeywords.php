<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\SaveEpisodeBeforeSave;
use App\Context\Episodes\Services\Internal\SaveEpisodeKeywords;

class SaveEpisodeBeforeSaveSaveEpisodeKeywords
{
    private SaveEpisodeKeywords $saveEpisodeKeywords;

    public function __construct(SaveEpisodeKeywords $saveEpisodeKeywords)
    {
        $this->saveEpisodeKeywords = $saveEpisodeKeywords;
    }

    public function onBeforeSave(SaveEpisodeBeforeSave $beforeSave): void
    {
        $this->saveEpisodeKeywords->save($beforeSave->episode);
    }
}
