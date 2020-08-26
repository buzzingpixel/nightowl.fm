<?php

declare(strict_types=1);

namespace App\Context\Episodes\Events;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Events\StoppableEvent;

class SaveEpisodeBeforeSave extends StoppableEvent
{
    public EpisodeModel $episode;
    public bool $isValid = true;

    public function __construct(EpisodeModel $episode)
    {
        $this->episode = $episode;
    }
}
