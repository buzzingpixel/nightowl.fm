<?php

declare(strict_types=1);

namespace App\Context\EpisodeDownloadStats\QueueJobs;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel as EpisodeFetchModel;
use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\QueueApi;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\ShowApi;
use Exception;

use function array_map;
use function count;

class CreateCalculationJobForShowEpisodesJob
{
    private ShowApi $showApi;
    private EpisodeApi $episodeApi;
    private QueueApi $queueApi;

    public function __construct(
        ShowApi $showApi,
        EpisodeApi $episodeApi,
        QueueApi $queueApi
    ) {
        $this->showApi    = $showApi;
        $this->episodeApi = $episodeApi;
        $this->queueApi   = $queueApi;
    }

    /**
     * @param mixed[] $context
     *
     * @throws Exception
     */
    public function __invoke(array $context): void
    {
        $showId = (string) ($context['showId'] ?? '');

        $showFetchModel = new ShowFetchModel();

        $showFetchModel->ids = [$showId];

        $show = $this->showApi->fetchShow($showFetchModel);

        if ($show === null) {
            throw new Exception(
                'Error calculating download stats when trying to ' .
                'find show with id ' . $showId,
            );
        }

        $episodeFetchModel = new EpisodeFetchModel();

        $episodeFetchModel->shows = [$show];

        $episodes = $this->episodeApi->fetchEpisodes(
            $episodeFetchModel
        );

        if (count($episodes) < 1) {
            return;
        }

        $queueModel = new QueueModel();

        $queueModel->handle = 'calculate-stats-for-show-episodes-' .
            $show->slug;

        $queueModel->displayName = 'Calculate stats for show episodes ' .
            $show->title;

        $queueModel->items = array_map(
            static function (EpisodeModel $e): QueueItemModel {
                $queueItem = new QueueItemModel();

                $queueItem->class = CalculateEpisodeStatsJob::class;

                $queueItem->context = ['episodeId' => $e->id];

                return $queueItem;
            },
            $episodes,
        );

        $this->queueApi->addToQueue($queueModel);
    }
}
