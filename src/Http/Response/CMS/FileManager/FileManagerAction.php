<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\FileManager;

use App\Context\FileManager\FileManagerApi;
use App\Http\Models\Meta;
use App\Http\Models\Pagination;
use App\Http\Utilities\Segments\ExtractUriSegments;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class FileManagerAction
{
    private const LIMIT = 50;

    private FileManagerApi $fileManagerApi;
    private ExtractUriSegments $extractUriSegments;
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;

    public function __construct(
        FileManagerApi $fileManagerApi,
        ExtractUriSegments $extractUriSegments,
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig
    ) {
        $this->fileManagerApi     = $fileManagerApi;
        $this->extractUriSegments = $extractUriSegments;
        $this->responseFactory    = $responseFactory;
        $this->twig               = $twig;
    }

    /**
     * @throws HttpNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $uriSegments = $this->extractUriSegments->extract(
            $request->getUri(),
        );

        if (
            $uriSegments->hasPaginationTrigger() &&
            $uriSegments->getPageNum() < 2
        ) {
            throw new HttpNotFoundException($request);
        }

        $pageNum = $uriSegments->getPageNum();

        $offset = ($pageNum * self::LIMIT) - self::LIMIT;

        $allFiles = $this->fileManagerApi->fetchAllFiles();

        $files = $allFiles->slice(self::LIMIT, $offset);

        if ($pageNum > 1 && $files->count() < 1) {
            throw new HttpNotFoundException($request);
        }

        $pagination = (new Pagination())
            ->withBase('/cms/file-manager')
            ->withCurrentPage($pageNum)
            ->withPerPage(self::LIMIT)
            ->withTotalResults($allFiles->count());

        $meta = new Meta();

        $meta->title = $title = 'File Manager';

        if ($pageNum > 1) {
            $meta->title .= ' | Page ' . $pageNum;

            $title .= '- Page ' . $pageNum;
        }

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/FileManager/Index.twig',
                [
                    'meta' => $meta,
                    'title' => $title,
                    'activeNavHref' => '/cms/file-manager',
                    'files' => $files,
                    'pagination' => $pagination,
                ],
            ),
        );

        return $response;
    }
}
