<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\NewShow;

use App\Context\Shows\Models\ShowModel;
use App\Http\Response\CMS\Shows\Shared\SaveShowFromPost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostNewShowAction
{
    private PostNewShowResponder $responder;
    private SaveShowFromPost $saveShowFromPost;

    public function __construct(
        PostNewShowResponder $responder,
        SaveShowFromPost $saveShowFromPost
    ) {
        $this->responder        = $responder;
        $this->saveShowFromPost = $saveShowFromPost;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->saveShowFromPost->save(
            $request,
            new ShowModel(),
            $this->responder,
        );
    }
}
