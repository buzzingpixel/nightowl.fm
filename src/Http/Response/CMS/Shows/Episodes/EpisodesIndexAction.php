<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Episodes;

use App\Context\Shows\Models\FetchModel as ShowsFetchModel;
use App\Context\Shows\ShowApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EpisodesIndexAction
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
     * @throws HttpNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $showsFetchModel = new ShowsFetchModel();

        $showId = (string) $request->getAttribute('showId');

        $showsFetchModel->ids = [$showId];

        $show = $this->showApi->fetchShow($showsFetchModel);

        if ($show === null) {
            throw new HttpNotFoundException($request);
        }

        $title = $show->title . ' Episodes';

        $meta = new Meta();

        $meta->title = $title . '  | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/Shows/Episodes/Index.twig',
                [
                    'meta' => $meta,
                    'title' => $title,
                    'activeNavHref' => '/cms/shows',
                    'breadcrumbs' => [
                        [
                            'href' => '/cms/shows',
                            'content' => 'Shows',
                        ],
                    ],
                    'show' => $show,
                ],
            ),
        );

        return $response;
    }
}
