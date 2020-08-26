<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\DeleteEpisodeBeforeDelete;
use App\Context\Episodes\Services\Internal\DeleteEpisodeKeywords;

class DeleteEpisodeBeforeDeleteDeleteKeywords
{
    private DeleteEpisodeKeywords $deleteEpisodeKeywords;

    public function __construct(DeleteEpisodeKeywords $deleteEpisodeKeywords)
    {
        $this->deleteEpisodeKeywords = $deleteEpisodeKeywords;
    }

    public function onBeforeDelete(DeleteEpisodeBeforeDelete $beforeDelete): void
    {
        $this->deleteEpisodeKeywords->delete($beforeDelete->episode);
    }
}
