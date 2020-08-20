<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Episodes\NewEpisode;

use App\Context\Episodes\EpisodeConstants;
use App\Context\People\PeopleApi;
use App\Context\Series\Models\FetchModel as SeriesFetchModel;
use App\Context\Series\SeriesApi;
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

class NewEpisodeAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private ShowApi $showApi;
    private PeopleApi $peopleApi;
    private SeriesApi $seriesApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        ShowApi $showApi,
        PeopleApi $peopleApi,
        SeriesApi $seriesApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->showApi         = $showApi;
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
        $showsFetchModel = new ShowsFetchModel();

        $showsFetchModel->ids = [(string) $request->getAttribute('showId')];

        $show = $this->showApi->fetchShow($showsFetchModel);

        if ($show === null) {
            throw new HttpNotFoundException($request);
        }

        $seriesFetchModel = new SeriesFetchModel();

        $seriesFetchModel->shows = [$show];

        $title = 'Create New Episode of ' . $show->title;

        $meta = new Meta();

        $meta->title = $title . ' | CMS';

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
                ],
            ),
        );

        return $response;
    }
}
