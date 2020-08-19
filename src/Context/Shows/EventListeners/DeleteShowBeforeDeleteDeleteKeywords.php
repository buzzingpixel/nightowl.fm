<?php

declare(strict_types=1);

namespace App\Context\Shows\EventListeners;

use App\Context\Shows\Events\DeleteShowBeforeDelete;
use App\Context\Shows\Services\Internal\DeleteShowKeywords;

class DeleteShowBeforeDeleteDeleteKeywords
{
    private DeleteShowKeywords $deleteShowKeywords;

    public function __construct(DeleteShowKeywords $deleteShowKeywords)
    {
        $this->deleteShowKeywords = $deleteShowKeywords;
    }

    public function onBeforeDelete(DeleteShowBeforeDelete $beforeDelete): void
    {
        $this->deleteShowKeywords->delete($beforeDelete->show);
    }
}
