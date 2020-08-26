<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\DeleteEpisodeBeforeDelete;
use App\Context\Episodes\Services\Internal\DeleteEpisodeFile;

class DeleteEpisodeBeforeDeleteDeleteFile
{
    private DeleteEpisodeFile $deleteEpisodeFile;

    public function __construct(DeleteEpisodeFile $deleteEpisodeFile)
    {
        $this->deleteEpisodeFile = $deleteEpisodeFile;
    }

    public function onBeforeDelete(DeleteEpisodeBeforeDelete $beforeDelete): void
    {
        $this->deleteEpisodeFile->delete($beforeDelete->episode);
    }
}
