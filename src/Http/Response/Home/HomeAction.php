<?php

declare(strict_types=1);

namespace App\Http\Response\Home;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\FetchModel;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private EpisodeApi $episodeApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        EpisodeApi $episodeApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->episodeApi      = $episodeApi;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(): ResponseInterface
    {
        $fetchModel = new FetchModel();

        $fetchModel->orderByPublishedAt = true;

        $fetchModel->isPublished = true;

        $fetchModel->limit = 10;

        $fetchModel->excludeEpisodesFromHiddenShows = true;

        $episodes = $this->episodeApi->fetchEpisodes($fetchModel);

        $response = $this->responseFactory->createResponse()
            ->withHeader('EnableStaticCache', 'true');

        $meta = new Meta();

        $response->getBody()->write(
            $this->twig->render(
                'Http/EpisodeShowcase.twig',
                [
                    'meta' => $meta,
                    'episodes' => $episodes,
                ]
            ),
        );

        return $response;
    }
}
