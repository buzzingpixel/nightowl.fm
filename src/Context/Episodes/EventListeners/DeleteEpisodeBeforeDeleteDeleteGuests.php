<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\DeleteEpisodeBeforeDelete;
use App\Context\Episodes\Services\Internal\DeleteEpisodeGuests;

class DeleteEpisodeBeforeDeleteDeleteGuests
{
    private DeleteEpisodeGuests $deleteEpisodeGuests;

    public function __construct(DeleteEpisodeGuests $deleteEpisodeGuests)
    {
        $this->deleteEpisodeGuests = $deleteEpisodeGuests;
    }

    public function onBeforeDelete(DeleteEpisodeBeforeDelete $beforeDelete): void
    {
        $this->deleteEpisodeGuests->delete($beforeDelete->episode);
    }
}
