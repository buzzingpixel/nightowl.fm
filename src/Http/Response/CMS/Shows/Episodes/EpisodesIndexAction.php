<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Episodes;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\FetchModel as EpisodeFetchModel;
use App\Context\Shows\Models\FetchModel as ShowsFetchModel;
use App\Context\Shows\ShowApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EpisodesIndexAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private ShowApi $showApi;
    private EpisodeApi $episodeApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        ShowApi $showApi,
        EpisodeApi $episodeApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->showApi         = $showApi;
        $this->episodeApi      = $episodeApi;
    }

    /**
     * @throws HttpNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $showsFetchModel = new ShowsFetchModel();

        $showId = (string) $request->getAttribute('showId');

        $showsFetchModel->ids = [$showId];

        $show = $this->showApi->fetchShow($showsFetchModel);

        if ($show === null) {
            throw new HttpNotFoundException($request);
        }

        $draftFetchModel           = new EpisodeFetchModel();
        $draftFetchModel->shows    = [$show];
        $draftFetchModel->statuses = [EpisodeConstants::EPISODE_STATUS_DRAFT];
        $drafts                    = $this->episodeApi->fetchEpisodes(
            $draftFetchModel
        );

        $scheduledFetchModel              = new EpisodeFetchModel();
        $scheduledFetchModel->shows       = [$show];
        $scheduledFetchModel->statuses    = [EpisodeConstants::EPISODE_STATUS_LIVE];
        $scheduledFetchModel->isPublished = false;
        $scheduled                        = $this->episodeApi->fetchEpisodes(
            $scheduledFetchModel
        );

        $publishedFetchModel              = new EpisodeFetchModel();
        $publishedFetchModel->shows       = [$show];
        $publishedFetchModel->isPublished = true;
        $published                        = $this->episodeApi->fetchEpisodes(
            $publishedFetchModel
        );

        $title = $show->title . ' Episodes';

        $meta = new Meta();

        $meta->title = $title . '  | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/Shows/Episodes/Index.twig',
                [
                    'meta' => $meta,
                    'title' => $title,
                    'activeNavHref' => '/cms/shows',
                    'breadcrumbs' => [
                        [
                            'href' => '/cms/shows',
                            'content' => 'Shows',
                        ],
                    ],
                    'show' => $show,
                    'drafts' => $drafts,
                    'scheduled' => $scheduled,
                    'published' => $published,
                ],
            ),
        );

        return $response;
    }
}
