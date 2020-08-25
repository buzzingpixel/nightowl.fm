<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel;

use function array_walk;

class PublishPendingEpisodes
{
    private EpisodeApi $episodeApi;

    public function __construct(EpisodeApi $episodeApi)
    {
        $this->episodeApi = $episodeApi;
    }

    public function __invoke(): void
    {
        $this->run();
    }

    public function run(): void
    {
        $fetchModel = new FetchModel();

        $fetchModel->isPublished = false;

        $fetchModel->statuses = [EpisodeConstants::EPISODE_STATUS_LIVE];

        $fetchModel->pastPublishedAt = true;

        $episodes = $this->episodeApi->fetchEpisodes($fetchModel);

        array_walk(
            $episodes,
            fn (EpisodeModel $e) => $this->episodeApi->saveEpisode(
                $e
            ),
        );
    }
}
