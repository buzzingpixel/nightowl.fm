<?php

declare(strict_types=1);

namespace App\Http\Response\Topics;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\FetchModel as EpisodeFetchModel;
use App\Context\Keywords\KeywordsApi;
use App\Context\Keywords\Models\FetchModel as KeywordFetchModel;
use App\Http\Models\Meta;
use App\Http\Models\Pagination;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment as TwigEnvironment;

use function count;

class TopicAction
{
    private const LIMIT = 10;

    private KeywordsApi $keywordsApi;
    private EpisodeApi $episodeApi;
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;

    public function __construct(
        KeywordsApi $keywordsApi,
        EpisodeApi $episodeApi,
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig
    ) {
        $this->keywordsApi     = $keywordsApi;
        $this->episodeApi      = $episodeApi;
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $pageNum = $this->getPageNumber($request);

        $offset = ($pageNum * self::LIMIT) - self::LIMIT;

        $topicSlug = (string) $request->getAttribute('topicSlug');

        $keywordFetchModel        = new KeywordFetchModel();
        $keywordFetchModel->slugs = [$topicSlug];

        $topic = $this->keywordsApi->fetchKeyword($keywordFetchModel);

        if ($topic === null) {
            throw new HttpNotFoundException($request);
        }

        $episodeFetchModel = new EpisodeFetchModel();

        $episodeFetchModel->keywords    = [$topic];
        $episodeFetchModel->isPublished = true;
        $episodeFetchModel->limit       = self::LIMIT;
        $episodeFetchModel->offset      = $offset;

        $episodes = $this->episodeApi->fetchEpisodes(
            $episodeFetchModel
        );

        if ($pageNum > 1 && count($episodes) < 1) {
            throw new HttpNotFoundException($request);
        }

        $totalEpisodes = $this->episodeApi->getTotal(
            $episodeFetchModel
        );

        /** @psalm-suppress MixedArgumentTypeCoercion */
        $pagination = (new Pagination())
            ->withBase($topic->getPublicUrl())
            ->withCurrentPage($pageNum)
            ->withPerPage(self::LIMIT)
            ->withTotalResults($totalEpisodes);

        $meta = new Meta();

        $meta->title = 'Episodes related to topic: ' . $topic->keyword;

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/EpisodeShowcase.twig',
                [
                    'meta' => $meta,
                    'topic' => $topic,
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
}
