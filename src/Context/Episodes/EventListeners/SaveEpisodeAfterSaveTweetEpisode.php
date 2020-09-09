<?php

declare(strict_types=1);

namespace App\Context\Episodes\EventListeners;

use App\Context\Episodes\Events\SaveEpisodeAfterSave;
use App\Context\Episodes\QueueJobs\TweetEpisode;
use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\QueueApi;

class SaveEpisodeAfterSaveTweetEpisode
{
    private QueueApi $queueApi;

    public function __construct(QueueApi $queueApi)
    {
        $this->queueApi = $queueApi;
    }

    public function onAfterSave(SaveEpisodeAfterSave $afterSave): void
    {
        if (! $afterSave->episode->tweetEpisode) {
            return;
        }

        $queueModel              = new QueueModel();
        $queueModel->handle      = 'tweet-episode';
        $queueModel->displayName = 'Tweet Episode';

        $queueItem          = new QueueItemModel();
        $queueItem->class   = TweetEpisode::class;
        $queueItem->context = ['episodeId' => $afterSave->episode->id];

        $queueModel->addItem($queueItem);

        $this->queueApi->addToQueue($queueModel);
    }
}
