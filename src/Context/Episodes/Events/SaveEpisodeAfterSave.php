<?php

declare(strict_types=1);

namespace App\Context\Episodes\Events;

use App\Context\Episodes\Models\EpisodeModel;

class SaveEpisodeAfterSave
{
    public EpisodeModel $episode;
    public bool $isValid = true;

    public function __construct(EpisodeModel $episode)
    {
        $this->episode = $episode;
    }
}
