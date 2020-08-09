<?php

declare(strict_types=1);

namespace App\Context\Schedule\Runners;

use App\Context\Schedule\Frequency;
use App\Context\TempFileStorage\Services\CleanUploadedFiles;

class CleanUploadedTempFiles extends CleanUploadedFiles
{
    public const RUN_EVERY = Frequency::TEN_MINUTES;
}
