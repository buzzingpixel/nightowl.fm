<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Series;

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

class SeriesIndexAction
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
        $fetchModel = new ShowsFetchModel();

        $fetchModel->ids = [(string) $request->getAttribute('showId')];

        $show = $this->showApi->fetchShow($fetchModel);

        if ($show === null) {
            throw new HttpNotFoundException($request);
        }

        $seriesFetchModel = new SeriesFetchModel();

        $seriesFetchModel->shows = [$show];

        $meta = new Meta();

        $meta->title = 'Series in ' . $show->title . ' | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/Shows/Series/Index.twig',
                [
                    'meta' => $meta,
                    'title' => 'Series in ' . $show->title,
                    'activeNavHref' => '/cms/shows',
                    'breadcrumbs' => [
                        [
                            'href' => '/cms/shows',
                            'content' => 'Shows',
                        ],
                    ],
                    'show' => $show,
                    'series' => $this->seriesApi->fetchSeries(),
                ],
            ),
        );

        return $response;
    }
}
