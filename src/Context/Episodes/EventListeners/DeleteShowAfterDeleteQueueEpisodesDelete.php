<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\QueueJobs\DeleteShowEpisodes;
use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\QueueApi;
use App\Context\Shows\Events\DeleteShowAfterDelete;

class DeleteShowAfterDeleteQueueEpisodesDelete
{
    private QueueApi $queueApi;

    public function __construct(QueueApi $queueApi)
    {
        $this->queueApi = $queueApi;
    }

    public function onAfterDelete(DeleteShowAfterDelete $afterDelete): void
    {
        $queueItem = new QueueItemModel();

        $queueItem->class = DeleteShowEpisodes::class;

        $queueItem->context = [
            'showId' => $afterDelete->show->id,
        ];

        $queueModel = new QueueModel();

        $queueModel->addItem($queueItem);

        $this->queueApi->addToQueue($queueModel);
    }
}
