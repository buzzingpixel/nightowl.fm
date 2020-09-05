<?php

declare(strict_types=1);

namespace App\Http\RouteMiddleware\RouteResolution;

use App\Context\Shows\Models\FetchModel as ShowsFetchModel;
use App\Context\Shows\ShowApi;
use App\Http\Response\Show\GetShowAction;
use App\Http\Utilities\Segments\ExtractUriSegments;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ResolveShow implements MiddlewareInterface
{
    private ExtractUriSegments $extractUriSegments;
    private ShowApi $showApi;
    private GetShowAction $getShowAction;

    public function __construct(
        ExtractUriSegments $extractUriSegments,
        ShowApi $showApi,
        GetShowAction $getShowAction
    ) {
        $this->extractUriSegments = $extractUriSegments;
        $this->showApi            = $showApi;
        $this->getShowAction      = $getShowAction;
    }

    /**
     * @throws HttpNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $uriSegments = $this->extractUriSegments->extract(
            $request->getUri()
        );

        if ($uriSegments->getTotalSegmentsSansPagination() > 1) {
            return $handler->handle($request);
        }

        $fetchModel = new ShowsFetchModel();

        $fetchModel->slugs[] = (string) $uriSegments->getSegment(1);

        $show = $this->showApi->fetchShow($fetchModel);

        if ($show === null) {
            return $handler->handle($request);
        }

        return $this->getShowAction->get(
            $uriSegments,
            $show,
            $request,
        );
    }
}
