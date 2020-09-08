<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\Services;

use App\Context\EpisodeDownloadStats\QueueJobs\MasterCreateCalculationJobForShowsJob;
use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\QueueApi;

class KickOffCalculateDownloadStats
{
    private QueueApi $queueApi;

    public function __construct(QueueApi $queueApi)
    {
        $this->queueApi = $queueApi;
    }

    public function __invoke(): void
    {
        $this->run();
    }

    public function run(): void
    {
        $queueModel = new QueueModel();

        $queueModel->handle = 'kick-off-calculate-download-stats';

        $queueModel->displayName = 'Kick off calculate download stats';

        $queueItemModel = new QueueItemModel();

        $queueItemModel->class = MasterCreateCalculationJobForShowsJob::class;

        $queueModel->addItem($queueItemModel);

        $this->queueApi->addToQueue($queueModel);
    }
}
