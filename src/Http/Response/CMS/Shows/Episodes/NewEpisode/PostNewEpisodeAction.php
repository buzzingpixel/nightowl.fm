<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Episodes\NewEpisode;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Shows\Models\FetchModel as ShowsFetchModel;
use App\Context\Shows\ShowApi;
use App\Http\Response\CMS\Shows\Episodes\Shared\SaveEpisodeFromPost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class PostNewEpisodeAction
{
    private PostNewEpisodeResponder $responder;
    private SaveEpisodeFromPost $saveEpisodeFromPost;
    private ShowApi $showApi;

    public function __construct(
        PostNewEpisodeResponder $responder,
        SaveEpisodeFromPost $saveEpisodeFromPost,
        ShowApi $showApi
    ) {
        $this->responder           = $responder;
        $this->saveEpisodeFromPost = $saveEpisodeFromPost;
        $this->showApi             = $showApi;
    }

    /**
     * @throws HttpNotFoundException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $showsFetchModel = new ShowsFetchModel();

        $showsFetchModel->ids = [(string) $request->getAttribute('showId')];

        $show = $this->showApi->fetchShow($showsFetchModel);

        if ($show === null) {
            throw new HttpNotFoundException($request);
        }

        return $this->saveEpisodeFromPost->save(
            $request,
            new EpisodeModel(),
            $this->responder,
            $show,
        );
    }
}
