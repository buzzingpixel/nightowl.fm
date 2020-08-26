<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\DeleteEpisodeBeforeDelete;
use App\Context\Episodes\Services\Internal\DeleteEpisodeHosts;

class DeleteEpisodeBeforeDeleteDeleteHosts
{
    private DeleteEpisodeHosts $deleteEpisodeHosts;

    public function __construct(DeleteEpisodeHosts $deleteEpisodeHosts)
    {
        $this->deleteEpisodeHosts = $deleteEpisodeHosts;
    }

    public function onBeforeDelete(DeleteEpisodeBeforeDelete $beforeDelete): void
    {
        $this->deleteEpisodeHosts->delete($beforeDelete->episode);
    }
}
