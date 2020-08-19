<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Series\NewSeries;

use App\Context\Series\Models\SeriesModel;
use App\Context\Shows\Models\FetchModel;
use App\Context\Shows\ShowApi;
use App\Http\Response\CMS\Shows\Series\Shared\SaveSeriesFromPost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class PostNewSeriesAction
{
    private PostNewSeriesResponder $responder;
    private SaveSeriesFromPost $saveSeriesFromPost;
    private ShowApi $showApi;

    public function __construct(
        PostNewSeriesResponder $responder,
        SaveSeriesFromPost $saveSeriesFromPost,
        ShowApi $showApi
    ) {
        $this->responder          = $responder;
        $this->saveSeriesFromPost = $saveSeriesFromPost;
        $this->showApi            = $showApi;
    }

    /**
     * @throws HttpNotFoundException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $fetchModel = new FetchModel();

        $fetchModel->ids = [(string) $request->getAttribute('showId')];

        $show = $this->showApi->fetchShow($fetchModel);

        if ($show === null) {
            throw new HttpNotFoundException($request);
        }

        return $this->saveSeriesFromPost->save(
            $request,
            new SeriesModel(),
            $this->responder,
            $show,
        );
    }
}
