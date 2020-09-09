<?php

declare(strict_types=1);

namespace App\Context\Analytics\Models;

class UriStatsModel
{
    public string $uri = '';

    public int $totalVisitorsInTimeRange = 0;

    public int $totalUniqueVisitorsInTimeRange = 0;
}
