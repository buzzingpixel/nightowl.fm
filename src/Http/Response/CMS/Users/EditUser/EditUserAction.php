<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Users\EditUser;

use App\Context\Users\UserApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Throwable;

class EditUserAction
{
    private EditUserResponder $responder;
    private UserApi $userApi;

    public function __construct(
        EditUserResponder $responder,
        UserApi $userApi
    ) {
        $this->responder = $responder;
        $this->userApi   = $userApi;
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

        $meta = new Meta();

        $meta->title = 'Edit ' . $user->emailAddress . ' | CMS';

        return $this->responder->respond(
            $meta,
            'Edit ' . $user->emailAddress,
            $user,
        );
    }
}
