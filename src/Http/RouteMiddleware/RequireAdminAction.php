<?php

declare(strict_types=1);

namespace App\Http\RouteMiddleware;

use App\Context\Users\Models\LoggedInUser;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequireAdminAction implements MiddlewareInterface
{
    private LoggedInUser $user;
    private RequireAdminResponder $responder;

    public function __construct(
        LoggedInUser $user,
        RequireAdminResponder $responder
    ) {
        $this->user      = $user;
        $this->responder = $responder;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (! $this->user->hasModel() || ! $this->user->model()->isAdmin) {
            $meta = new Meta();

            $meta->title = 'Unauthorized';

            return $this->responder->__invoke($meta);
        }

        return $handler->handle($request);
    }
}
