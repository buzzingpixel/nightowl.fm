<?php

declare(strict_types=1);

namespace App\Context\Schedule\Runners;

use App\Context\Queue\Services\CleanOldItems;
use App\Context\Schedule\Frequency;

class QueueCleanOldItems extends CleanOldItems
{
    public const RUN_EVERY = Frequency::DAY_AT_MIDNIGHT;
}
