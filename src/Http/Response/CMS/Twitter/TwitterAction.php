<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Twitter;

use App\Context\Twitter\TwitterApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TwitterAction
{
    private TwitterApi $twitterApi;
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twigEnvironment;

    public function __construct(
        TwitterApi $twitterApi,
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twigEnvironment
    ) {
        $this->twitterApi      = $twitterApi;
        $this->responseFactory = $responseFactory;
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Authorize Twitter | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twigEnvironment->render(
            'Http/CMS/Twitter/TwitterAuth.twig',
            [
                'meta' => $meta,
                'title' => 'Authorize Twitter',
                'activeNavHref' => '/cms/twitter',
                'twitterSettings' => $this->twitterApi->fetchTwitterSettings(),
            ]
        ));

        return $response;
    }
}
