<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Episodes\EditEpisode;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\FetchModel as EpisodeFetchModel;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\ShowApi;
use App\Http\Response\CMS\Shows\Episodes\Shared\SaveEpisodeFromPost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class PostEditEpisodeAction
{
    private PostEditEpisodeResponder $responder;
    private SaveEpisodeFromPost $saveEpisodeFromPost;
    private ShowApi $showApi;
    private EpisodeApi $episodeApi;

    public function __construct(
        PostEditEpisodeResponder $responder,
        SaveEpisodeFromPost $saveEpisodeFromPost,
        ShowApi $showApi,
        EpisodeApi $episodeApi
    ) {
        $this->responder           = $responder;
        $this->saveEpisodeFromPost = $saveEpisodeFromPost;
        $this->showApi             = $showApi;
        $this->episodeApi          = $episodeApi;
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

        return $this->saveEpisodeFromPost->save(
            $request,
            $episode,
            $this->responder,
            $show,
        );
    }
}
