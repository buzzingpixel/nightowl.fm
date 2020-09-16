<?php

declare(strict_types=1);

namespace App\Http\Response\Search;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\FetchModel;
use App\Http\Models\Meta;
use App\Http\Models\Pagination;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

use function count;

class SearchAction
{
    private const LIMIT = 10;

    private EpisodeApi $episodeApi;
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;

    public function __construct(
        EpisodeApi $episodeApi,
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig
    ) {
        $this->episodeApi      = $episodeApi;
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
    }

    /**
     * @throws HttpNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $pageNum = $this->getPageNumber($request);

        $offset = ($pageNum * self::LIMIT) - self::LIMIT;

        $query = $this->getSearchQuery($request);

        $fetchModel              = new FetchModel();
        $fetchModel->limit       = self::LIMIT;
        $fetchModel->offset      = $offset;
        $fetchModel->isPublished = true;
        $fetchModel->search      = '%' . $query . '%';

        $episodes = $this->episodeApi->fetchEpisodes($fetchModel);

        if ($pageNum > 1 && count($episodes) < 1) {
            throw new HttpNotFoundException($request);
        }

        $totalEpisodes = $this->episodeApi->getTotal($fetchModel);

        /** @psalm-suppress MixedArgumentTypeCoercion */
        $pagination = (new Pagination())
            ->withBase('/search')
            ->withQueryStringFromArray($request->getQueryParams())
            ->withCurrentPage($pageNum)
            ->withPerPage(self::LIMIT)
            ->withTotalResults($totalEpisodes);

        $meta = new Meta();

        $meta->title = 'Your search results';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/EpisodeShowcase.twig',
                [
                    'meta' => $meta,
                    'searchQuery' => $query,
                    'episodes' => $episodes,
                    'pagination' => $pagination,
                ],
            ),
        );

        return $response;
    }

    /**
     * @throws HttpNotFoundException
     */
    private function getPageNumber(ServerRequestInterface $request): int
    {
        /** @psalm-suppress MixedAssignment */
        $pageNum = $request->getAttribute('pageNum');
        $pageNum = $pageNum !== null ? ((int) $pageNum) : null;

        if ($pageNum === 1) {
            throw new HttpNotFoundException($request);
        }

        $pageNum ??= 1;

        return $pageNum;
    }

    /**
     * @throws HttpNotFoundException
     */
    private function getSearchQuery(ServerRequestInterface $request): string
    {
        $query = (string) ($request->getQueryParams()['q'] ?? '');

        if ($query === '') {
            throw new HttpNotFoundException($request);
        }

        return $query;
    }
}
