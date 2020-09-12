<?php

declare(strict_types=1);

namespace App\Http\Response\Shows;

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

class GetShowsAction
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
        $activeFetchModel = new FetchModel();

        $activeFetchModel->notStatuses = [
            ShowConstants::SHOW_STATUS_HIDDEN,
            ShowConstants::SHOW_STATUS_RETIRED,
        ];

        $activeShows = $this->showApi->fetchShows(
            $activeFetchModel
        );

        $retiredFetchModel           = new FetchModel();
        $retiredFetchModel->statuses = [ShowConstants::SHOW_STATUS_RETIRED];

        $retiredShows = $this->showApi->fetchShows(
            $retiredFetchModel
        );

        $response = $this->responseFactory->createResponse()
            ->withHeader('EnableStaticCache', 'true');

        $meta = new Meta();

        $meta->title = 'Shows';

        $response->getBody()->write(
            $this->twig->render(
                'Http/Shows.twig',
                [
                    'meta' => $meta,
                    'activeShows' => $activeShows,
                    'retiredShows' => $retiredShows,
                ]
            ),
        );

        return $response;
    }
}
