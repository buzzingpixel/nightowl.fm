<?php

declare(strict_types=1);

namespace App\Http\Response\Show;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\FetchModel as EpisodeFetchModel;
use App\Context\Series\Models\SeriesModel;
use App\Context\Shows\ShowApi;
use App\Http\Models\Meta;
use App\Http\Models\Pagination;
use App\Http\Utilities\Segments\UriSegments;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

use function count;

class GetSeriesAction
{
    private const LIMIT = 10;

    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private EpisodeApi $episodeApi;
    private ShowApi $showApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        EpisodeApi $episodeApi,
        ShowApi $showApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->episodeApi      = $episodeApi;
        $this->showApi         = $showApi;
    }

    /**
     * @throws HttpNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function get(
        UriSegments $uriSegments,
        SeriesModel $series,
        ServerRequestInterface $request
    ): ResponseInterface {
        $meta = new Meta();

        $meta->title = $series->getTitleWithShowTitle();

        $meta->description = $series->description;

        $meta->twitterCardType = 'summary_large_image';

        $meta->shareImage = $this->showApi->getShowArtworkUrl(
            $series->show
        );

        $offset = ($uriSegments->getPageNum() * self::LIMIT) - self::LIMIT;

        $episodeFetchModel         = new EpisodeFetchModel();
        $episodeFetchModel->shows  = [$series->show];
        $episodeFetchModel->series = [$series];
        $episodeFetchModel->limit  = self::LIMIT;
        $episodeFetchModel->offset = $offset;

        $episodes = $this->episodeApi->fetchEpisodes(
            $episodeFetchModel,
        );

        if ($uriSegments->getPageNum() > 1 && count($episodes) < 1) {
            throw new HttpNotFoundException($request);
        }

        $totalEpisodes = $this->episodeApi->getTotal(
            $episodeFetchModel
        );

        $pagination = (new Pagination())
            ->withBase($series->getPublicUrl())
            ->withCurrentPage($uriSegments->getPageNum())
            ->withPerPage(self::LIMIT)
            ->withTotalResults($totalEpisodes);

        $response = $this->responseFactory->createResponse()
            ->withHeader('EnableStaticCache', 'true');

        $response->getBody()->write(
            $this->twig->render(
                'Http/Series.twig',
                [
                    'meta' => $meta,
                    'series' => $series,
                    'episodes' => $episodes,
                    'pagination' => $pagination,
                ],
            ),
        );

        return $response;
    }
}
