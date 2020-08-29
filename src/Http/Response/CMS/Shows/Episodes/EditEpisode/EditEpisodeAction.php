<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Episodes\EditEpisode;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\FetchModel as EpisodeFetchModel;
use App\Context\People\PeopleApi;
use App\Context\Series\Models\FetchModel as SeriesFetchModel;
use App\Context\Series\SeriesApi;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
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

class EditEpisodeAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private ShowApi $showApi;
    private EpisodeApi $episodeApi;
    private PeopleApi $peopleApi;
    private SeriesApi $seriesApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        ShowApi $showApi,
        EpisodeApi $episodeApi,
        PeopleApi $peopleApi,
        SeriesApi $seriesApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->showApi         = $showApi;
        $this->episodeApi      = $episodeApi;
        $this->peopleApi       = $peopleApi;
        $this->seriesApi       = $seriesApi;
    }

    /**
     * @throws HttpNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
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

        $seriesFetchModel = new SeriesFetchModel();

        $meta = new Meta();

        $title = 'Edit ' .
            $show->title .
            ' Episode: ' .
            (
                $episode->title !== '' ? $episode->title : 'Draft'
            );

        $meta->title = $title . ' | CMS';

        $deleteAction = '';

        if (! $episode->isPublished) {
            $deleteAction = '/cms/shows/episodes/' .
                $show->id .
                '/delete/' .
                $episode->id;
        }

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/Shows/Episodes/EditEpisode.twig',
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
                    'statusOptions' => EpisodeConstants::STATUSES_SELECT_ARRAY,
                    'typeOptions' => EpisodeConstants::TYPES_SELECT_ARRAY,
                    'peopleOptions' => $this->peopleApi->transformPersonModelsToSelectArray(
                        $this->peopleApi->fetchPeople()
                    ),
                    'seriesOptions' => $this->seriesApi->transformSeriesModelsToSelectArray(
                        $this->seriesApi->fetchSeries(
                            $seriesFetchModel
                        )
                    ),
                    'show' => $show,
                    'episode' => $episode,
                    'deleteAction' => $deleteAction,
                ],
            ),
        );

        return $response;
    }
}
