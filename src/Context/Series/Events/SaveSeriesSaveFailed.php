<?php

declare(strict_types=1);

namespace App\Context\Series\Events;

use App\Context\Series\Models\SeriesModel;

class SaveSeriesSaveFailed
{
    public SeriesModel $series;

    public function __construct(SeriesModel $series)
    {
        $this->series = $series;
    }
}
