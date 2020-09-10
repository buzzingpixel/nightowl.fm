<?php

declare(strict_types=1);

namespace App\Templating\TwigExtensions;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel;
use App\Context\Shows\Models\ShowModel;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetEpisodesForShow extends AbstractExtension
{
    private EpisodeApi $episodeApi;

    public function __construct(EpisodeApi $episodeApi)
    {
        $this->episodeApi = $episodeApi;
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'getEpisodesForShow',
                [$this, 'getEpisodesForShow']
            ),
            new TwigFunction(
                'getEpisodesForShowCount',
                [$this, 'getEpisodesForShowCount']
            ),
        ];
    }

    /**
     * @return EpisodeModel[]
     */
    public function getEpisodesForShow(ShowModel $show, ?int $limit = null): array
    {
        $fetchModel = new FetchModel();

        $fetchModel->shows = [$show];

        $fetchModel->isPublished = true;

        $fetchModel->limit = $limit;

        return $this->episodeApi->fetchEpisodes($fetchModel);
    }

    public function getEpisodesForShowCount(ShowModel $show): int
    {
        $fetchModel = new FetchModel();

        $fetchModel->shows = [$show];

        $fetchModel->isPublished = true;

        return $this->episodeApi->getTotal($fetchModel);
    }
}
