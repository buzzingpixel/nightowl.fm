<?php

declare(strict_types=1);

namespace App\Context\Episodes\QueueJobs;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\FetchModel;

class TweetEpisode
{
    private EpisodeApi $episodeApi;

    public function __construct(EpisodeApi $episodeApi)
    {
        $this->episodeApi = $episodeApi;
    }

    /**
     * @param mixed[] $context
     */
    public function __invoke(array $context): void
    {
        $episodeId = (string) ($context['episodeId'] ?? '');

        $fetchModel = new FetchModel();

        $fetchModel->ids = [$episodeId];

        $episode = $this->episodeApi->fetchEpisode($fetchModel);

        if ($episode === null) {
            return;
        }

        $this->episodeApi->tweetEpisode($episode);
    }
}
