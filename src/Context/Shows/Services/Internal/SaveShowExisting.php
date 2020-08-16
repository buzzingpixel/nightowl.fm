<?php

declare(strict_types=1);

namespace App\Context\Shows\Services\Internal;

use App\Context\Shows\Models\ShowModel;
use App\Payload\Payload;

use function dd;

class SaveShowExisting
{
    public function save(ShowModel $show): Payload
    {
        dd($show);
    }
}
