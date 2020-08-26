<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Episodes\DeleteEpisode;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\FetchModel as EpisodeFetchModel;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\ShowApi;
use App\Payload\Payload;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Flash\Messages as FlashMessages;

class PostDeleteEpisodeAction
{
    private ShowApi $showApi;
    private FlashMessages $flashMessages;
    private ResponseFactoryInterface $responseFactory;
    private EpisodeApi $episodeApi;

    public function __construct(
        ShowApi $showApi,
        EpisodeApi $episodeApi,
        FlashMessages $flashMessages,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->showApi         = $showApi;
        $this->flashMessages   = $flashMessages;
        $this->responseFactory = $responseFactory;
        $this->episodeApi      = $episodeApi;
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

        $payload = $this->episodeApi->deleteEpisode($episode);

        if ($payload->getStatus() !== Payload::STATUS_DELETED) {
            $this->flashMessages->addMessage(
                'PostMessage',
                [
                    'status' => $payload->getStatus(),
                    'result' => $payload->getResult(),
                ]
            );

            return $this->responseFactory->createResponse(303)
                ->withHeader(
                    'Location',
                    '/cms/shows/episodes/' . $show->id . '/edit/' . $episodeId,
                );
        }

        $this->flashMessages->addMessage(
            'PostMessage',
            [
                'status' => Payload::STATUS_SUCCESSFUL,
                'result' => ['message' => 'Episode deleted successfully'],
            ]
        );

        return $this->responseFactory->createResponse(303)
            ->withHeader(
                'Location',
                '/cms/shows/episodes/' . $show->id,
            );
    }
}
