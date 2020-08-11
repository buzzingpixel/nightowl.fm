<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People;

use App\Context\People\PeopleApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseInterface;

class PeopleIndexAction
{
    private PeopleIndexResponder $responder;
    private PeopleApi $peopleApi;

    public function __construct(
        PeopleIndexResponder $responder,
        PeopleApi $peopleApi
    ) {
        $this->responder = $responder;
        $this->peopleApi = $peopleApi;
    }

    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Shows | CMS';

        return $this->responder->respond(
            $meta,
            'People',
            $this->peopleApi->fetchPeople(),
        );
    }
}
