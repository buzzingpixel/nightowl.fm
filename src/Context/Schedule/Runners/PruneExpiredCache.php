<?php

declare(strict_types=1);

namespace App\Context\Schedule\Runners;

use App\Context\DatabaseCache\Services\PruneExpiredCache as PruneExpiredCacheService;
use App\Context\Schedule\Frequency;

class PruneExpiredCache extends PruneExpiredCacheService
{
    public const RUN_EVERY = Frequency::ALWAYS;
}
