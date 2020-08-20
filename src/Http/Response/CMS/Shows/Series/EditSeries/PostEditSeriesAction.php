<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Series\EditSeries;

use App\Context\Series\Models\FetchModel as SeriesFetchModel;
use App\Context\Series\SeriesApi;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\ShowApi;
use App\Http\Response\CMS\Shows\Series\Shared\SaveSeriesFromPost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class PostEditSeriesAction
{
    private PostEditSeriesResponder $responder;
    private SaveSeriesFromPost $saveSeriesFromPost;
    private ShowApi $showApi;
    private SeriesApi $seriesApi;

    public function __construct(
        PostEditSeriesResponder $responder,
        SaveSeriesFromPost $saveSeriesFromPost,
        ShowApi $showApi,
        SeriesApi $seriesApi
    ) {
        $this->responder          = $responder;
        $this->saveSeriesFromPost = $saveSeriesFromPost;
        $this->showApi            = $showApi;
        $this->seriesApi          = $seriesApi;
    }

    /**
     * @throws HttpNotFoundException
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

        return $this->saveSeriesFromPost->save(
            $request,
            $series,
            $this->responder,
            $show,
        );
    }
}
