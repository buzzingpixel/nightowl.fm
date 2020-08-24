<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\Models\EpisodeModel;

use function time;

class SetEpisodePublishedState
{
    public function set(EpisodeModel $episode): void
    {
        if ($episode->isPublished) {
            return;
        }

        if ($episode->publishAt === null) {
            $episode->isPublished = true;

            return;
        }

        if (time() < $episode->publishAt->getTimestamp()) {
            return;
        }

        $episode->isPublished = true;
    }
}
