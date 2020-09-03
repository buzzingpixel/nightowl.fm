<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Pages\EditPage;

use App\Context\Pages\Models\FetchModel;
use App\Context\Pages\PagesApi;
use App\Http\Response\CMS\Pages\Shared\SavePageFromPost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class PostEditPageAction
{
    private PagesApi $pagesApi;
    private PostEditPageResponder $responder;
    private SavePageFromPost $saveFromPost;

    public function __construct(
        PagesApi $pagesApi,
        PostEditPageResponder $responder,
        SavePageFromPost $saveFromPost
    ) {
        $this->pagesApi     = $pagesApi;
        $this->responder    = $responder;
        $this->saveFromPost = $saveFromPost;
    }

    /**
     * @throws HttpNotFoundException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $fetchModel = new FetchModel();

        $fetchModel->ids = [(string) $request->getAttribute('id')];

        $page = $this->pagesApi->fetchPage($fetchModel);

        if ($page === null) {
            throw new HttpNotFoundException($request);
        }

        return $this->saveFromPost->save(
            $request,
            $page,
            $this->responder
        );
    }
}
