<?php

declare(strict_types=1);

namespace App\Context\Schedule\Runners;

use App\Context\Schedule\Frequency;
use App\Context\Users\Services\SessionGarbageCollection;

class UserSessionGarbageCollection extends SessionGarbageCollection
{
    public const RUN_EVERY = Frequency::DAY_AT_MIDNIGHT;
}
