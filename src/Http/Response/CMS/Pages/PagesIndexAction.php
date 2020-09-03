<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Pages;

use App\Context\Pages\PagesApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PagesIndexAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private PagesApi $pagesApi;

    public function __construct(
        PagesApi $pagesApi,
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->pagesApi        = $pagesApi;
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

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/Pages/Index.twig',
                [
                    'meta' => $meta,
                    'title' => 'Shows',
                    'activeNavHref' => '/cms/pages',
                    'pages' => $this->pagesApi->fetchPages(),
                ],
            ),
        );

        return $response;
    }
}
