<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\DeleteEpisodeBeforeDelete;
use App\Context\Episodes\Services\Internal\DeleteEpisodeSeries;

class DeleteEpisodeBeforeDeleteDeleteSeries
{
    private DeleteEpisodeSeries $deleteEpisodeSeries;

    public function __construct(DeleteEpisodeSeries $deleteEpisodeSeries)
    {
        $this->deleteEpisodeSeries = $deleteEpisodeSeries;
    }

    public function onBeforeDelete(DeleteEpisodeBeforeDelete $beforeDelete): void
    {
        $this->deleteEpisodeSeries->delete($beforeDelete->episode);
    }
}
