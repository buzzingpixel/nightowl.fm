<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Models;

use App\Context\Episodes\Models\EpisodeModel;

class EpisodeDownloadStatsModel
{
    public string $id = '';

    public EpisodeModel $episode;

    public int $totalDownloads = 0;

    public int $downloadsPastThirtyDays = 0;

    public int $downloadsPastSixtyDays = 0;

    public int $downloadsPastYear = 0;
}
