<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows;

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

class ShowsIndexAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private ShowApi $showApi;

    public function __construct(
        ShowApi $showApi,
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig
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
        $meta = new Meta();

        $meta->title = 'Shows | CMS';

        $response = $this->responseFactory->createResponse();

        $activeFetchModel              = new FetchModel();
        $activeFetchModel->notStatuses = [ShowConstants::SHOW_STATUS_RETIRED];

        $activeShows = $this->showApi->fetchShows(
            $activeFetchModel
        );

        $retiredFetchModel           = new FetchModel();
        $retiredFetchModel->statuses = [ShowConstants::SHOW_STATUS_RETIRED];

        $retiredShows = $this->showApi->fetchShows(
            $retiredFetchModel
        );

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/Shows/Index.twig',
                [
                    'meta' => $meta,
                    'title' => 'Shows',
                    'activeNavHref' => '/cms/shows',
                    'activeShows' => $activeShows,
                    'retiredShows' => $retiredShows,
                ],
            ),
        );

        return $response;
    }
}
