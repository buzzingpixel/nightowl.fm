<?php

declare(strict_types=1);

namespace App\Context\Episodes\QueueJobs;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel;

use function array_walk;
use function count;

class DeleteShowEpisodes
{
    private EpisodeApi $episodeApi;

    public function __construct(EpisodeApi $episodeApi)
    {
        $this->episodeApi = $episodeApi;
    }

    /**
     * @param array<string, string> $context
     */
    public function __invoke(array $context): void
    {
        $showId = $context['showId'] ?? '';

        if ($showId === '') {
            return;
        }

        $fetchModel = new FetchModel();

        $fetchModel->showIds = [$showId];

        $episodes = $this->episodeApi->fetchEpisodes($fetchModel);

        if (count($episodes) < 1) {
            return;
        }

        array_walk(
            $episodes,
            fn (EpisodeModel $e) => $this->episodeApi->deleteEpisode(
                $e
            ),
        );
    }
}
