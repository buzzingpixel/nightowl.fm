<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows;

use Psr\Http\Message\ResponseInterface;

class ShowsAction
{
    private ShowsResponder $responder;

    public function __construct(ShowsResponder $responder)
    {
        $this->responder = $responder;
    }

    public function __invoke(): ResponseInterface
    {
        return $this->responder->__invoke();
    }
}
