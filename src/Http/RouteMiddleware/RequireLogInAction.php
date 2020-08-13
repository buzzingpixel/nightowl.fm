<?php

declare(strict_types=1);

namespace App\Http\RouteMiddleware;

use App\Context\Users\UserApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class RequireLogInAction implements MiddlewareInterface
{
    private UserApi $userApi;
    private RequireLogInResponder $responder;

    public function __construct(
        UserApi $userApi,
        RequireLogInResponder $responder
    ) {
        $this->userApi   = $userApi;
        $this->responder = $responder;
    }

    /**
     * @throws Throwable
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $user = $this->userApi->fetchLoggedInUser();

        $meta = new Meta();

        $meta->title = 'Log In';

        if ($user === null || ! $user->isActive) {
            return $this->responder->respond(
                $meta,
                $request->getUri()->getPath()
            );
        }

        return $handler->handle($request);
    }
}
