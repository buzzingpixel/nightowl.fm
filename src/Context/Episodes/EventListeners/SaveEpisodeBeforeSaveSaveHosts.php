<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\SaveEpisodeBeforeSave;
use App\Context\Episodes\Services\Internal\SaveEpisodeHosts;

class SaveEpisodeBeforeSaveSaveHosts
{
    private SaveEpisodeHosts $saveEpisodeHosts;

    public function __construct(SaveEpisodeHosts $saveEpisodeHosts)
    {
        $this->saveEpisodeHosts = $saveEpisodeHosts;
    }

    public function onBeforeSave(SaveEpisodeBeforeSave $beforeSave): void
    {
        $this->saveEpisodeHosts->save($beforeSave->episode);
    }
}
