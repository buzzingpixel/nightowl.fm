<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Users\EditUser;

use App\Context\Users\UserApi;
use App\Http\Response\CMS\Users\Shared\SaveUserFromPost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Throwable;

class PostEditUserAction
{
    private PostEditUserResponder $responder;
    private UserApi $userApi;
    private SaveUserFromPost $saveUserFromPost;

    public function __construct(
        PostEditUserResponder $responder,
        UserApi $userApi,
        SaveUserFromPost $saveUserFromPost
    ) {
        $this->responder        = $responder;
        $this->userApi          = $userApi;
        $this->saveUserFromPost = $saveUserFromPost;
    }

    /**
     * @throws HttpNotFoundException
     * @throws Throwable
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->userApi->fetchUserById(
            (string) $request->getAttribute('id')
        );

        if ($user === null) {
            throw new HttpNotFoundException($request);
        }

        return $this->saveUserFromPost->save(
            $request,
            $user,
            $this->responder,
        );
    }
}
