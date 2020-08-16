<?php

declare(strict_types=1);

namespace App\Context\Shows\Events;

use App\Context\Events\StoppableEvent;
use App\Context\Shows\Models\ShowModel;

class SaveShowAfterSave extends StoppableEvent
{
    public ShowModel $show;
    public bool $isValid = true;

    public function __construct(ShowModel $show)
    {
        $this->show = $show;
    }
}
