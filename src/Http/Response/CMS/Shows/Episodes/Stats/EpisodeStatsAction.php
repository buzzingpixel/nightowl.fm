<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Episodes\Stats;

use App\Context\EpisodeDownloadStats\EpisodeDownloadStatsApi;
use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\FetchModel as EpisodeFetchModel;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\ShowApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment as TwigEnvironment;

class EpisodeStatsAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private ShowApi $showApi;
    private EpisodeApi $episodeApi;
    private EpisodeDownloadStatsApi $statsApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        ShowApi $showApi,
        EpisodeApi $episodeApi,
        EpisodeDownloadStatsApi $statsApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->showApi         = $showApi;
        $this->episodeApi      = $episodeApi;
        $this->statsApi        = $statsApi;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $showFetchModel = new ShowFetchModel();

        $showId = (string) $request->getAttribute('showId');

        $showFetchModel->ids = [$showId];

        $show = $this->showApi->fetchShow($showFetchModel);

        if ($show === null) {
            throw new HttpNotFoundException($request);
        }

        $episodeId = (string) $request->getAttribute('episodeId');

        $episodeFetchModel = new EpisodeFetchModel();

        $episodeFetchModel->ids = [$episodeId];

        $episodeFetchModel->shows = [$show];

        $episode = $this->episodeApi->fetchEpisode(
            $episodeFetchModel
        );

        if ($episode === null) {
            throw new HttpNotFoundException($request);
        }

        $stats = $this->statsApi->fetchStatsForEpisode($episode);

        $meta = new Meta();

        $title = 'Stats for ' . $episode->getNumberedTitle();

        $meta->title = $title . ' | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/Shows/Episodes/Stats.twig',
                [
                    'meta' => $meta,
                    'title' => $title,
                    'activeNavHref' => '/cms/shows',
                    'breadcrumbs' => [
                        [
                            'href' => '/cms/shows',
                            'content' => 'Shows',
                        ],
                        [
                            'href' => '/cms/shows/episodes/' . $show->id,
                            'content' => $show->title . ' Episodes',
                        ],
                    ],
                    'episode' => $episode,
                    'episodeStats' => $stats,
                ],
            ),
        );

        return $response;
    }
}
