<?php

declare(strict_types=1);

namespace App\Context\Schedule\Runners;

use App\Context\Queue\Services\CleanDeadItems;
use App\Context\Schedule\Frequency;

class QueueCleanDeadItems extends CleanDeadItems
{
    public const RUN_EVERY = Frequency::ALWAYS;
}
