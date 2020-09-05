<?php

declare(strict_types=1);

namespace App\Http\Response\Show;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Shows\ShowApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GetEpisodeAction
{
    private ShowApi $showApi;
    private EpisodeApi $episodeApi;
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;

    public function __construct(
        ShowApi $showApi,
        EpisodeApi $episodeApi,
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig
    ) {
        $this->showApi         = $showApi;
        $this->episodeApi      = $episodeApi;
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function get(EpisodeModel $episode): ResponseInterface
    {
        $response = $this->responseFactory->createResponse()
            ->withHeader('EnableStaticCache', 'true');

        $meta = new Meta();

        $meta->title = $episode->getNumberedTitleWithShow();

        $response->getBody()->write(
            $this->twig->render(
                'Http/Episode.twig',
                [
                    'meta' => $meta,
                    'episode' => $episode,
                ],
            ),
        );

        return $response;
    }
}
