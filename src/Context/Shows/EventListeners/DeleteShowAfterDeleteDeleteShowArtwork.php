<?php

declare(strict_types=1);

namespace App\Context\Shows\EventListeners;

use App\Context\Shows\Events\DeleteShowAfterDelete;
use App\Context\Shows\Services\DeleteShowArtwork;

class DeleteShowAfterDeleteDeleteShowArtwork
{
    private DeleteShowArtwork $deleteShowArtwork;

    public function __construct(DeleteShowArtwork $deleteShowArtwork)
    {
        $this->deleteShowArtwork = $deleteShowArtwork;
    }

    public function onAfterDelete(DeleteShowAfterDelete $afterDelete): void
    {
        $this->deleteShowArtwork->delete($afterDelete->show);
    }
}
