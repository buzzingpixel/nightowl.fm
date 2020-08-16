<?php

declare(strict_types=1);

namespace App\Context\Shows\Events;

use App\Context\Events\StoppableEvent;
use App\Context\Shows\Models\ShowModel;

class SaveShowSaveFailed extends StoppableEvent
{
    public ShowModel $show;

    public function __construct(ShowModel $show)
    {
        $this->show = $show;
    }
}
