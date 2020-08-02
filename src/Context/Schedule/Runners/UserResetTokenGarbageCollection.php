<?php

declare(strict_types=1);

namespace App\Context\Schedule\Runners;

use App\Context\Schedule\Frequency;
use App\Context\Users\Services\ResetTokenGarbageCollection;

class UserResetTokenGarbageCollection extends ResetTokenGarbageCollection
{
    public const RUN_EVERY = Frequency::FIVE_MINUTES;
}
