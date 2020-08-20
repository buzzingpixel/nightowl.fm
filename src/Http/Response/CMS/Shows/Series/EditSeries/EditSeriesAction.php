<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Series\EditSeries;

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

class EditSeriesAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private ShowApi $showApi;
    private SeriesApi $seriesApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        ShowApi $showApi,
        SeriesApi $seriesApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->showApi         = $showApi;
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

        $seriesId = (string) $request->getAttribute('seriesId');

        $seriesFetchModel = new SeriesFetchModel();

        $seriesFetchModel->ids = [$seriesId];

        $seriesFetchModel->shows = [$show];

        $series = $this->seriesApi->fetchOneSeries(
            $seriesFetchModel
        );

        if ($series === null) {
            throw new HttpNotFoundException($request);
        }

        $meta = new Meta();

        $title = 'Edit ' . $show->title . ' Series: ' . $series->title;

        $meta->title = $title . ' | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/Shows/Series/EditSeries.twig',
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
                            'href' => '/cms/shows/series/' . $show->id,
                            'content' => 'Series in ' . $show->title,
                        ],
                    ],
                    'show' => $show,
                    'series' => $series,
                ],
            ),
        );

        return $response;
    }
}
