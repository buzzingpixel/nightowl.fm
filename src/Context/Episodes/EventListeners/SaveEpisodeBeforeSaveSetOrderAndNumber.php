<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\SaveEpisodeBeforeSave;
use App\Context\Episodes\Services\Internal\SetEpisodeOrderAndNumber;

class SaveEpisodeBeforeSaveSetOrderAndNumber
{
    private SetEpisodeOrderAndNumber $setEpisodeOrderAndNumber;

    public function __construct(
        SetEpisodeOrderAndNumber $setEpisodeOrderAndNumber
    ) {
        $this->setEpisodeOrderAndNumber = $setEpisodeOrderAndNumber;
    }

    public function onBeforeSave(SaveEpisodeBeforeSave $beforeSave): void
    {
        $this->setEpisodeOrderAndNumber->set(
            $beforeSave->episode
        );
    }
}
