<?php

declare(strict_types=1);

namespace App\Context\Series\Events;

use App\Context\Series\Models\SeriesModel;

class SaveSeriesAfterSave
{
    public SeriesModel $series;
    public bool $isValid = true;

    public function __construct(SeriesModel $series)
    {
        $this->series = $series;
    }
}
