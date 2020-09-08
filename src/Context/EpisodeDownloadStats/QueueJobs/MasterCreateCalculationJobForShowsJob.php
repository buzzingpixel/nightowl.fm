<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\QueueJobs;

use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\QueueApi;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\ShowApi;

use function array_map;
use function count;

class MasterCreateCalculationJobForShowsJob
{
    private ShowApi $showApi;
    private QueueApi $queueApi;

    public function __construct(
        ShowApi $showApi,
        QueueApi $queueApi
    ) {
        $this->showApi  = $showApi;
        $this->queueApi = $queueApi;
    }

    public function __invoke(): void
    {
        $shows = $this->showApi->fetchShows();

        if (count($shows) < 1) {
            return;
        }

        $queueModel = new QueueModel();

        $queueModel->handle = 'master-create-calculation-job-for-shows';

        $queueModel->displayName = 'Master create calculation job for shows';

        $queueModel->items = array_map(
            static function (ShowModel $show): QueueItemModel {
                $queueItem = new QueueItemModel();

                $queueItem->class = CreateCalculationJobForShowEpisodesJob::class;

                $queueItem->context = ['showId' => $show->id];

                return $queueItem;
            },
            $shows
        );

        $this->queueApi->addToQueue($queueModel);
    }
}
