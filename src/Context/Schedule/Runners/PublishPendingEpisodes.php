<?php

declare(strict_types=1);

namespace App\Context\Schedule\Runners;

use App\Context\Episodes\Services\PublishPendingEpisodes as PublishPendingEpisodesService;
use App\Context\Schedule\Frequency;

class PublishPendingEpisodes extends PublishPendingEpisodesService
{
    public const RUN_EVERY = Frequency::ALWAYS;
}
