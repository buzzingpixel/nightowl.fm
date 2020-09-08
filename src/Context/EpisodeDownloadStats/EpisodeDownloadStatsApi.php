<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadStatsModel;
use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadTrackerModel;
use App\Context\EpisodeDownloadStats\Services\CalculateStatsForEpisode;
use App\Context\EpisodeDownloadStats\Services\FetchStatsForEpisode;
use App\Context\EpisodeDownloadStats\Services\FetchTrackersForEpisode;
use App\Context\EpisodeDownloadStats\Services\KickOffCalculateDownloadStats;
use App\Context\EpisodeDownloadStats\Services\SaveStats;
use App\Context\EpisodeDownloadStats\Services\SaveTracker;
use App\Context\Episodes\Models\EpisodeModel;
use App\Payload\Payload;
use Psr\Container\ContainerInterface;

use function assert;

class EpisodeDownloadStatsApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function saveTracker(EpisodeDownloadTrackerModel $model): Payload
    {
        $service = $this->di->get(SaveTracker::class);

        assert($service instanceof SaveTracker);

        return $service->save($model);
    }

    /**
     * @return EpisodeDownloadTrackerModel[]
     */
    public function fetchTrackersForEpisode(EpisodeModel $episode): array
    {
        $service = $this->di->get(FetchTrackersForEpisode::class);

        assert($service instanceof FetchTrackersForEpisode);

        return $service->fetch($episode);
    }

    public function calculateStatsForEpisode(EpisodeModel $episode): void
    {
        $service = $this->di->get(CalculateStatsForEpisode::class);

        assert($service instanceof CalculateStatsForEpisode);

        $service->calculate($episode);
    }

    public function saveStats(EpisodeDownloadStatsModel $model): Payload
    {
        $service = $this->di->get(SaveStats::class);

        assert($service instanceof SaveStats);

        return $service->save($model);
    }

    public function fetchStatsForEpisode(
        EpisodeModel $episode
    ): ?EpisodeDownloadStatsModel {
        $service = $this->di->get(FetchStatsForEpisode::class);

        assert($service instanceof FetchStatsForEpisode);

        return $service->fetch($episode);
    }

    public function kickOffCalculateDownloadStats(): void
    {
        $service = $this->di->get(KickOffCalculateDownloadStats::class);

        assert($service instanceof KickOffCalculateDownloadStats);

        $service->run();
    }
}
