<?php

declare(strict_types=1);

namespace App\Context\Series\Models;

use App\Context\Shows\Models\ShowModel;

class SeriesModel
{
    public string $id = '';

    public string $title = '';

    public string $slug = '';

    public string $description = '';

    public ShowModel $show;
}
