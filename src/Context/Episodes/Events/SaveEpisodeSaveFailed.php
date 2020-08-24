<?php

declare(strict_types=1);

namespace App\Context\Episodes\Events;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Events\StoppableEvent;

class SaveEpisodeSaveFailed extends StoppableEvent
{
    public EpisodeModel $episode;

    public function __construct(EpisodeModel $episode)
    {
        $this->episode = $episode;
    }
}
