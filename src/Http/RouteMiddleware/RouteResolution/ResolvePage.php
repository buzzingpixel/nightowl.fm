<?php

declare(strict_types=1);

namespace App\Http\RouteMiddleware\RouteResolution;

use App\Context\Pages\Models\FetchModel as PagesFetchModel;
use App\Context\Pages\PagesApi;
use App\Http\Response\Pages\GetPageAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ResolvePage implements MiddlewareInterface
{
    private PagesApi $pagesApi;
    private GetPageAction $getPageAction;

    public function __construct(
        PagesApi $pagesApi,
        GetPageAction $getPageAction
    ) {
        $this->pagesApi      = $pagesApi;
        $this->getPageAction = $getPageAction;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $fetchModel = new PagesFetchModel();

        $fetchModel->uris[] = $request->getUri()->getPath();

        $page = $this->pagesApi->fetchPage($fetchModel);

        if ($page === null) {
            return $handler->handle($request);
        }

        return $this->getPageAction->get($page);
    }
}
