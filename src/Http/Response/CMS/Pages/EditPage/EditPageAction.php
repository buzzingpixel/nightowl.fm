<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Pages\EditPage;

use App\Context\Pages\Models\FetchModel;
use App\Context\Pages\PagesApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EditPageAction
{
    private ResponseFactoryInterface $responseFactory;
    private Environment $twig;
    private PagesApi $pagesApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        Environment $twig,
        PagesApi $pagesApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->pagesApi        = $pagesApi;
    }

    /**
     * @throws HttpNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $fetchModel = new FetchModel();

        $fetchModel->ids = [(string) $request->getAttribute('id')];

        $page = $this->pagesApi->fetchPage($fetchModel);

        if ($page === null) {
            throw new HttpNotFoundException($request);
        }

        $meta = new Meta();

        $meta->title = 'Edit ' . $page->title . ' | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twig->render(
            'Http/CMS/Pages/EditPage.twig',
            [
                'meta' => $meta,
                'title' => 'Edit ' . $page->title,
                'activeNavHref' => '/cms/pages',
                'deleteAction' => '/cms/pages/delete/' . $page->id,
                'breadcrumbs' => [
                    [
                        'href' => '/cms/pages',
                        'content' => 'Pages',
                    ],
                ],
                'page' => $page,
            ]
        ));

        return $response;
    }
}
