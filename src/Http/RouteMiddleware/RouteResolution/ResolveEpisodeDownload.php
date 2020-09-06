<?php

declare(strict_types=1);

namespace App\Http\RouteMiddleware\RouteResolution;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel as EpisodeFetchModel;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\ShowApi;
use App\Http\Response\Show\GetEpisodeDownloadAction;
use App\Http\Utilities\Segments\ExtractUriSegments;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function is_numeric;

class ResolveEpisodeDownload implements MiddlewareInterface
{
    private ExtractUriSegments $extractUriSegments;
    private GetEpisodeDownloadAction $getEpisodeDownloadAction;
    private ShowApi $showApi;
    private EpisodeApi $episodeApi;

    public function __construct(
        ExtractUriSegments $extractUriSegments,
        GetEpisodeDownloadAction $getEpisodeDownloadAction,
        ShowApi $showApi,
        EpisodeApi $episodeApi
    ) {
        $this->extractUriSegments       = $extractUriSegments;
        $this->getEpisodeDownloadAction = $getEpisodeDownloadAction;
        $this->showApi                  = $showApi;
        $this->episodeApi               = $episodeApi;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $uriSegments = $this->extractUriSegments->extract(
            $request->getUri()
        );

        $totalSegments = $uriSegments->getTotalSegments();

        if ($totalSegments !== 3) {
            return $handler->handle($request);
        }

        $show = $this->getShow(
            (string) $uriSegments->getSegment(1)
        );

        if ($show === null) {
            return $handler->handle($request);
        }

        $episode = $this->getEpisode(
            $show,
            (string) $uriSegments->getSegment(2)
        );

        if (
            $episode === null ||
            $episode->getFileName() !== $uriSegments->getSegment(3)
        ) {
            return $handler->handle($request);
        }

        return $this->getEpisodeDownloadAction->get(
            $request,
            $episode
        );
    }

    private function getShow(string $showSlug): ?ShowModel
    {
        $fetchModel = new ShowFetchModel();

        $fetchModel->slugs = [$showSlug];

        return $this->showApi->fetchShow($fetchModel);
    }

    private function getEpisode(
        ShowModel $show,
        string $idOrNumber
    ): ?EpisodeModel {
        $fetchModel             = new EpisodeFetchModel();
        $fetchModel->statuses[] = EpisodeConstants::EPISODE_STATUS_LIVE;

        $fetchModel->shows = [$show];

        if (is_numeric($idOrNumber)) {
            $fetchModel->episodeNumbers = [(int) $idOrNumber];
        } else {
            $fetchModel->ids = [$idOrNumber];
        }

        return $this->episodeApi->fetchEpisode($fetchModel);
    }
}
