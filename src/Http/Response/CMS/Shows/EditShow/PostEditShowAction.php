<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\EditShow;

use App\Context\Shows\Models\FetchModel;
use App\Context\Shows\ShowApi;
use App\Http\Response\CMS\Shows\Shared\SaveShowFromPost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class PostEditShowAction
{
    private ShowApi $showApi;
    private PostEditShowResponder $responder;
    private SaveShowFromPost $saveShowFromPost;

    public function __construct(
        ShowApi $showApi,
        PostEditShowResponder $responder,
        SaveShowFromPost $saveShowFromPost
    ) {
        $this->showApi          = $showApi;
        $this->responder        = $responder;
        $this->saveShowFromPost = $saveShowFromPost;
    }

    /**
     * @throws HttpNotFoundException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $fetchModel = new FetchModel();

        $fetchModel->ids = [(string) $request->getAttribute('id')];

        $show = $this->showApi->fetchShow($fetchModel);

        if ($show === null) {
            throw new HttpNotFoundException($request);
        }

        return $this->saveShowFromPost->save(
            $request,
            $show,
            $this->responder,
        );
    }
}
