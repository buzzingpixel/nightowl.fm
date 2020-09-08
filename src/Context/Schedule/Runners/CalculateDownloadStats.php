<?php

declare(strict_types=1);

namespace App\Context\Schedule\Runners;

use App\Context\EpisodeDownloadStats\Services\KickOffCalculateDownloadStats;
use App\Context\Schedule\Frequency;

class CalculateDownloadStats extends KickOffCalculateDownloadStats
{
    public const RUN_EVERY = Frequency::DAY_AT_MIDNIGHT;
}
