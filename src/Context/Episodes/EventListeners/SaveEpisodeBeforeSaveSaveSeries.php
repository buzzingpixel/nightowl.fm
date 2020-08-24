<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\SaveEpisodeBeforeSave;
use App\Context\Episodes\Services\Internal\SaveEpisodeSeries;

class SaveEpisodeBeforeSaveSaveSeries
{
    private SaveEpisodeSeries $saveEpisodeSeries;

    public function __construct(SaveEpisodeSeries $saveEpisodeSeries)
    {
        $this->saveEpisodeSeries = $saveEpisodeSeries;
    }

    public function onBeforeSave(SaveEpisodeBeforeSave $beforeSave): void
    {
        $this->saveEpisodeSeries->save($beforeSave->episode);
    }
}
