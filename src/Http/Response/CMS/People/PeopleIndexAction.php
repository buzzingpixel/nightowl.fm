<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseInterface;

class PeopleIndexAction
{
    private PeopleIndexResponder $responder;

    public function __construct(PeopleIndexResponder $responder)
    {
        $this->responder = $responder;
    }

    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Shows | CMS';

        return $this->responder->__invoke(
            $meta,
            'People',
        );
    }
}
