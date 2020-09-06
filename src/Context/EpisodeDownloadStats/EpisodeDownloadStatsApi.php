<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats;

use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadTrackerModel;
use App\Context\EpisodeDownloadStats\Services\SaveTracker;
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
}
