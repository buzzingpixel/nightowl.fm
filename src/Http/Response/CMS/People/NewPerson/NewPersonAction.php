<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People\NewPerson;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseInterface;

class NewPersonAction
{
    private NewPersonResponder $responder;

    public function __construct(
        NewPersonResponder $responder
    ) {
        $this->responder = $responder;
    }

    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Create New Person | CMS';

        return $this->responder->__invoke(
            $meta,
            'Create New Person',
        );
    }
}
