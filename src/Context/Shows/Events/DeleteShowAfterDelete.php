<?php

declare(strict_types=1);

namespace App\Context\Shows\Events;

use App\Context\Shows\Models\ShowModel;

class DeleteShowAfterDelete
{
    public ShowModel $show;
    public bool $isValid = true;

    public function __construct(ShowModel $show)
    {
        $this->show = $show;
    }
}
