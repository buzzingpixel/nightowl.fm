<?php

declare(strict_types=1);

namespace App\Http\Response\Show;

use App\Context\Episodes\Models\EpisodeModel;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GetEpisodeAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig
    ) {
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
