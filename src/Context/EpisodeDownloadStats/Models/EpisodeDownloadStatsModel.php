<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Models;

use App\Context\Episodes\Models\EpisodeModel;
use DateTimeZone;
use Safe\DateTimeImmutable;

class EpisodeDownloadStatsModel
{
    public function __construct()
    {
        $this->lastUpdatedAt = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );
    }

    public string $id = '';

    /** @psalm-suppress PropertyNotSetInConstructor */
    public EpisodeModel $episode;

    public int $totalDownloads = 0;

    public int $downloadsPastThirtyDays = 0;

    public int $downloadsPastSixtyDays = 0;

    public int $downloadsPastYear = 0;

    public DateTimeImmutable $lastUpdatedAt;
}
