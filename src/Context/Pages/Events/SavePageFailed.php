<?php

declare(strict_types=1);

namespace App\Context\Pages\Events;

use App\Context\Events\StoppableEvent;
use App\Context\Pages\Models\PageModel;

class SavePageFailed extends StoppableEvent
{
    public PageModel $person;

    public function __construct(PageModel $page)
    {
        $this->person = $page;
    }
}
