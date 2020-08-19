<?php

declare(strict_types=1);

namespace App\Context\Shows\EventListeners;

use App\Context\Shows\Events\DeleteShowBeforeDelete;
use App\Context\Shows\Services\Internal\DeleteShowHosts;

class DeleteShowBeforeDeleteDeleteHosts
{
    private DeleteShowHosts $deleteShowHosts;

    public function __construct(DeleteShowHosts $deleteShowHosts)
    {
        $this->deleteShowHosts = $deleteShowHosts;
    }

    public function onBeforeDelete(DeleteShowBeforeDelete $beforeDelete): void
    {
        $this->deleteShowHosts->delete($beforeDelete->show);
    }
}
