<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People\DeletePerson;

use App\Context\People\Models\FetchModel;
use App\Context\People\PeopleApi;
use App\Payload\Payload;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Flash\Messages as FlashMessages;

class PostDeletePersonAction
{
    private PeopleApi $peopleApi;
    private FlashMessages $flashMessages;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        PeopleApi $peopleApi,
        FlashMessages $flashMessages,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->peopleApi       = $peopleApi;
        $this->flashMessages   = $flashMessages;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @throws HttpNotFoundException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $fetchModel = new FetchModel();

        $fetchModel->ids = [(string) $request->getAttribute('id')];

        $person = $this->peopleApi->fetchPerson($fetchModel);

        if ($person === null) {
            throw new HttpNotFoundException($request);
        }

        $payload = $this->peopleApi->deletePerson($person);

        if ($payload->getStatus() !== Payload::STATUS_DELETED) {
            $this->flashMessages->addMessage(
                'PostMessage',
                [
                    'status' => $payload->getStatus(),
                    'result' => $payload->getResult(),
                ]
            );

            return $this->responseFactory->createResponse(303)
                ->withHeader(
                    'Location',
                    '/cms/people/edit/' . $person->id,
                );
        }

        $this->flashMessages->addMessage(
            'PostMessage',
            [
                'status' => Payload::STATUS_SUCCESSFUL,
                'result' => ['message' => $person->getFullName() . ' deleted successfully'],
            ]
        );

        return $this->responseFactory->createResponse(303)
            ->withHeader(
                'Location',
                '/cms/people',
            );
    }
}
