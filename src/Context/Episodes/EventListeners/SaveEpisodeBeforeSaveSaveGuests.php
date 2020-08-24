<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\SaveEpisodeBeforeSave;
use App\Context\Episodes\Services\Internal\SaveEpisodeGuests;

class SaveEpisodeBeforeSaveSaveGuests
{
    private SaveEpisodeGuests $saveEpisodeGuests;

    public function __construct(SaveEpisodeGuests $saveEpisodeGuests)
    {
        $this->saveEpisodeGuests = $saveEpisodeGuests;
    }

    public function onBeforeSave(SaveEpisodeBeforeSave $beforeSave): void
    {
        $this->saveEpisodeGuests->save($beforeSave->episode);
    }
}
