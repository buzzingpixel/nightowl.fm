<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseInterface;

class ShowsIndexAction
{
    private ShowsIndexResponder $responder;

    public function __construct(ShowsIndexResponder $responder)
    {
        $this->responder = $responder;
    }

    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Shows | CMS';

        return $this->responder->__invoke(
            $meta,
            'Shows',
        );
    }
}
