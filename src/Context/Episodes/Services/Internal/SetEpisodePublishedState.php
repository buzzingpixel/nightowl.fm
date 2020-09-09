<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services\Internal;

use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\EpisodeModel;
use DateTimeZone;
use Safe\DateTimeImmutable;

use function time;

class SetEpisodePublishedState
{
    public function set(EpisodeModel $episode): void
    {
        if ($episode->isPublished) {
            return;
        }

        if ($episode->status !== EpisodeConstants::EPISODE_STATUS_LIVE) {
            return;
        }

        if ($episode->publishAt === null) {
            $this->publishEpisode($episode);

            return;
        }

        if (time() < $episode->publishAt->getTimestamp()) {
            return;
        }

        $this->publishEpisode($episode);
    }

    private function publishEpisode(EpisodeModel $episode): void
    {
        $episode->isPublished = true;

        $episode->tweetEpisode = true;

        /** @noinspection PhpUnhandledExceptionInspection */
        $episode->publishedAt = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC'),
        );
    }
}
