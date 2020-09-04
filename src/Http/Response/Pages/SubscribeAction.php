<?php

declare(strict_types=1);

namespace App\Http\Response\Pages;

use App\Context\Shows\Models\FetchModel;
use App\Context\Shows\ShowApi;
use App\Context\Shows\ShowConstants;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SubscribeAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private ShowApi $showApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        ShowApi $showApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->showApi         = $showApi;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse()
            ->withHeader('EnableStaticCache', 'true');

        $meta = new Meta();

        $meta->title = 'Subscribe';

        $activeFetchModel             = new FetchModel();
        $activeFetchModel->statuses[] = ShowConstants::SHOW_STATUS_LIVE;

        $retiredFetchModel             = new FetchModel();
        $retiredFetchModel->statuses[] = ShowConstants::SHOW_STATUS_RETIRED;

        $response->getBody()->write(
            $this->twig->render(
                'Http/Subscribe.twig',
                [
                    'meta' => $meta,
                    'activeShows' => $this->showApi->fetchShows(
                        $activeFetchModel,
                    ),
                    'retiredShows' => $this->showApi->fetchShows(
                        $retiredFetchModel,
                    ),
                ],
            ),
        );

        return $response;
    }
}
