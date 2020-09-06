<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Models;

use App\Context\Episodes\Models\EpisodeModel;
use DateTimeZone;
use Safe\DateTimeImmutable;

class EpisodeDownloadTrackerModel
{
    public function __construct()
    {
        $this->downloadedAt = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );
    }

    public string $id = '';

    /** @psalm-suppress PropertyNotSetInConstructor */
    public EpisodeModel $episode;

    public bool $isFullRange = false;

    public int $rangeStart = 0;

    public int $rangeEnd = 0;

    public DateTimeImmutable $downloadedAt;
}
