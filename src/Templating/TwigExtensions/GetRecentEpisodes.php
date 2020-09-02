<?php

declare(strict_types=1);

namespace App\Templating\TwigExtensions;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetRecentEpisodes extends AbstractExtension
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
        return [$this->getFunction()];
    }

    private function getFunction(): TwigFunction
    {
        return new TwigFunction(
            'getRecentEpisodes',
            [$this, 'getRecentEpisodes']
        );
    }

    /**
     * @return EpisodeModel[]
     */
    public function getRecentEpisodes(int $limit = 5): array
    {
        $fetchModel = new FetchModel();

        $fetchModel->orderByPublishedAt = true;

        $fetchModel->statuses = [EpisodeConstants::EPISODE_STATUS_LIVE];

        $fetchModel->limit = $limit;

        return $this->episodeApi->fetchEpisodes($fetchModel);
    }
}
