<?php

declare(strict_types=1);

namespace App\Http\Response\Home;

use Psr\Http\Message\ResponseInterface;

class HomeAction
{
    private HomeResponder $responder;

    public function __construct(HomeResponder $responder)
    {
        $this->responder = $responder;
    }

    public function __invoke(): ResponseInterface
    {
        return $this->responder->__invoke();
    }
}
