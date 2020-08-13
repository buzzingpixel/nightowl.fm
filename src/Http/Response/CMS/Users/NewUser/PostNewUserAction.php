<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Users\NewUser;

use App\Context\Users\Models\UserModel;
use App\Http\Response\CMS\Users\Shared\SaveUserFromPost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostNewUserAction
{
    private PostNewUserResponder $responder;
    private SaveUserFromPost $saveUserFromPost;

    public function __construct(
        PostNewUserResponder $responder,
        SaveUserFromPost $saveUserFromPost
    ) {
        $this->responder        = $responder;
        $this->saveUserFromPost = $saveUserFromPost;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->saveUserFromPost->save(
            $request,
            new UserModel(),
            $this->responder,
        );
    }
}
