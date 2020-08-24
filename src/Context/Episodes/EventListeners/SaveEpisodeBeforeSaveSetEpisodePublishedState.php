<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\SaveEpisodeBeforeSave;
use App\Context\Episodes\Services\Internal\SetEpisodePublishedState;

class SaveEpisodeBeforeSaveSetEpisodePublishedState
{
    private SetEpisodePublishedState $setEpisodePublishedState;

    public function __construct(
        SetEpisodePublishedState $setEpisodePublishedState
    ) {
        $this->setEpisodePublishedState = $setEpisodePublishedState;
    }

    public function onBeforeSave(SaveEpisodeBeforeSave $beforeSave): void
    {
        $this->setEpisodePublishedState->set($beforeSave->episode);
    }
}
