<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\QueueJobs;

use App\Context\EpisodeDownloadStats\EpisodeDownloadStatsApi;
use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\FetchModel;
use Exception;

class CalculateEpisodeStatsJob
{
    private EpisodeApi $episodeApi;
    private EpisodeDownloadStatsApi $statsApi;

    public function __construct(
        EpisodeApi $episodeApi,
        EpisodeDownloadStatsApi $statsApi
    ) {
        $this->episodeApi = $episodeApi;
        $this->statsApi   = $statsApi;
    }

    /**
     * @param mixed[] $context
     *
     * @throws Exception
     */
    public function __invoke(array $context): void
    {
        $episodeId = (string) ($context['episodeId'] ?? '');

        $fetchModel = new FetchModel();

        $fetchModel->ids = [$episodeId];

        $episode = $this->episodeApi->fetchEpisode($fetchModel);

        if ($episode === null) {
            throw new Exception(
                'Error calculating download stats when trying to ' .
                'find episode with id ' . $episodeId,
            );
        }

        $this->statsApi->calculateStatsForEpisode($episode);
    }
}
