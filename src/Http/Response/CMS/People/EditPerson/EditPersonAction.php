<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People\EditPerson;

use App\Context\People\Models\FetchModel;
use App\Context\People\PeopleApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

use function count;

class EditPersonAction
{
    private EditPersonResponder $responder;
    private PeopleApi $peopleApi;

    public function __construct(
        EditPersonResponder $responder,
        PeopleApi $peopleApi
    ) {
        $this->responder = $responder;
        $this->peopleApi = $peopleApi;
    }

    /**
     * @throws HttpNotFoundException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $fetchModel = new FetchModel();

        $fetchModel->ids = [(string) $request->getAttribute('id')];

        if (count($fetchModel->ids) < 1) {
            throw new HttpNotFoundException($request);
        }

        $person = $this->peopleApi->fetchPerson($fetchModel);

        if ($person === null) {
            throw new HttpNotFoundException($request);
        }

        $meta = new Meta();

        $meta->title = 'Edit ' . $person->getFullName() . ' | CMS';

        return $this->responder->respond(
            $meta,
            'Edit ' . $person->getFullName(),
            $person,
        );
    }
}
