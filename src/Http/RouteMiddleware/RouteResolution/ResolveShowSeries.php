<?php

declare(strict_types=1);

namespace App\Http\RouteMiddleware\RouteResolution;

use App\Context\Series\Models\FetchModel as SeriesFetchModel;
use App\Context\Series\SeriesApi;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\ShowApi;
use App\Http\Response\Show\GetSeriesAction;
use App\Http\Utilities\Segments\ExtractUriSegments;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ResolveShowSeries implements MiddlewareInterface
{
    private ExtractUriSegments $extractUriSegments;
    private ShowApi $showApi;
    private SeriesApi $seriesApi;
    private GetSeriesAction $action;

    public function __construct(
        ExtractUriSegments $extractUriSegments,
        ShowApi $showApi,
        SeriesApi $seriesApi,
        GetSeriesAction $action
    ) {
        $this->extractUriSegments = $extractUriSegments;
        $this->showApi            = $showApi;
        $this->seriesApi          = $seriesApi;
        $this->action             = $action;
    }

    /**
     * @throws HttpNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $uriSegments = $this->extractUriSegments->extract(
            $request->getUri()
        );

        if ($uriSegments->getTotalSegmentsSansPagination() > 3) {
            return $handler->handle($request);
        }

        $showFetchModel          = new ShowFetchModel();
        $showFetchModel->slugs[] = (string) $uriSegments->getSegment(1);

        $show = $this->showApi->fetchShow($showFetchModel);

        if ($show === null) {
            return $handler->handle($request);
        }

        $seriesFetchModel          = new SeriesFetchModel();
        $seriesFetchModel->shows   = [$show];
        $seriesFetchModel->slugs[] = (string) $uriSegments->getSegment(3);

        $series = $this->seriesApi->fetchOneSeries(
            $seriesFetchModel
        );

        if ($series === null) {
            return $handler->handle($request);
        }

        return $this->action->get(
            $uriSegments,
            $series,
            $request,
        );
    }
}
