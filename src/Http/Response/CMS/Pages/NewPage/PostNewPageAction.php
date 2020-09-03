<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Pages\NewPage;

use App\Context\Pages\Models\PageModel;
use App\Http\Response\CMS\Pages\Shared\SavePageFromPost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostNewPageAction
{
    private PostNewPageResponder $responder;
    private SavePageFromPost $saveFromPost;

    public function __construct(
        PostNewPageResponder $responder,
        SavePageFromPost $saveFromPost
    ) {
        $this->responder    = $responder;
        $this->saveFromPost = $saveFromPost;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->saveFromPost->save(
            $request,
            new PageModel(),
            $this->responder
        );
    }
}
