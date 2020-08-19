<?php

declare(strict_types=1);

namespace App\Context\Series\Events;

use App\Context\Events\StoppableEvent;
use App\Context\Series\Models\SeriesModel;

class SaveSeriesBeforeSave extends StoppableEvent
{
    public SeriesModel $series;
    public bool $isValid = true;

    public function __construct(SeriesModel $series)
    {
        $this->series = $series;
    }
}
