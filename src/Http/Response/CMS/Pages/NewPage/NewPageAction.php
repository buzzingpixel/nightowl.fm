<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Pages\NewPage;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class NewPageAction
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
    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Create New Show | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twig->render(
            'Http/CMS/Pages/EditPage.twig',
            [
                'meta' => $meta,
                'title' => 'Create New Page',
                'activeNavHref' => '/cms/pages',
                'breadcrumbs' => [
                    [
                        'href' => '/cms/pages',
                        'content' => 'Pages',
                    ],
                ],
            ]
        ));

        return $response;
    }
}
