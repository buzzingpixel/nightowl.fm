<?php

declare(strict_types=1);

namespace App\Http\Response;

use App\Context\Pages\Models\FetchModel as PagesFetchModel;
use App\Context\Pages\PagesApi;
use App\Context\Shows\Models\FetchModel as ShowsFetchModel;
use App\Context\Shows\ShowApi;
use App\Http\Response\Pages\GetPageAction;
use App\Http\Response\Shows\GetShowAction;
use App\Http\Utilities\Segments\ExtractUriSegments;
use App\Http\Utilities\Segments\UriSegments;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class ResolveShowOrPage
{
    private ExtractUriSegments $extractUriSegments;
    private ShowApi $showApi;
    private PagesApi $pagesApi;
    private GetShowAction $getShowAction;
    private GetPageAction $getPageAction;

    public function __construct(
        ExtractUriSegments $extractUriSegments,
        ShowApi $showApi,
        PagesApi $pagesApi,
        GetShowAction $getShowAction,
        GetPageAction $getPageAction
    ) {
        $this->extractUriSegments = $extractUriSegments;
        $this->showApi            = $showApi;
        $this->pagesApi           = $pagesApi;
        $this->getShowAction      = $getShowAction;
        $this->getPageAction      = $getPageAction;
    }

    /**
     * @throws HttpNotFoundException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $uriSegments = $this->extractUriSegments->extract(
            $request->getUri()
        );

        // Resolve show only if this is a paginated page
        if ($uriSegments->getPageNum() > 1) {
            if ($uriSegments->getTotalSegmentsSansPagination() > 1) {
                throw new HttpNotFoundException($request);
            }

            $response = $this->resolveShow($uriSegments);

            if ($response === null) {
                throw new HttpNotFoundException($request);
            }

            return $response;
        }

        // Resolve page only if there are more segments than 1
        if ($uriSegments->getTotalSegments() > 1) {
            $response = $this->resolvePage($uriSegments);

            if ($response === null) {
                throw new HttpNotFoundException($request);
            }

            return $response;
        }

        /**
         * Resolve show first, if no show, resolve page
         */

        $response = $this->resolveShow($uriSegments);

        if ($response !== null) {
            return $response;
        }

        $response = $this->resolvePage($uriSegments);

        if ($response !== null) {
            return $response;
        }

        throw new HttpNotFoundException($request);
    }

    private function resolveShow(UriSegments $uriSegments): ?ResponseInterface
    {
        $fetchModel = new ShowsFetchModel();

        $fetchModel->slugs[] = (string) $uriSegments->getSegment(1);

        $show = $this->showApi->fetchShow($fetchModel);

        if ($show === null) {
            return null;
        }

        return $this->getShowAction->get(
            $uriSegments,
            $show
        );
    }

    private function resolvePage(UriSegments $uriSegments): ?ResponseInterface
    {
        $fetchModel = new PagesFetchModel();

        $fetchModel->uris[] = '/' . $uriSegments->getPath();

        $page = $this->pagesApi->fetchPage($fetchModel);

        if ($page === null) {
            return null;
        }

        return $this->getPageAction->get($page);
    }
}
