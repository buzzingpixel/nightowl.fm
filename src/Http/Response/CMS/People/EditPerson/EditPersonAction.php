<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People\EditPerson;

use App\Context\People\Models\FetchModel;
use App\Context\People\PeopleApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EditPersonAction
{
    private PeopleApi $peopleApi;
    private ResponseFactoryInterface $responseFactory;
    private Environment $twig;

    public function __construct(
        PeopleApi $peopleApi,
        ResponseFactoryInterface $responseFactory,
        Environment $twig
    ) {
        $this->peopleApi       = $peopleApi;
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
    }

    /**
     * @throws HttpNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $fetchModel = new FetchModel();

        $fetchModel->ids = [(string) $request->getAttribute('id')];

        $person = $this->peopleApi->fetchPerson($fetchModel);

        if ($person === null) {
            throw new HttpNotFoundException($request);
        }

        $meta = new Meta();

        $meta->title = 'Edit ' . $person->getFullName() . ' | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twig->render(
            'Http/CMS/People/EditPerson.twig',
            [
                'meta' => $meta,
                'title' => 'Edit ' . $person->getFullName(),
                'activeNavHref' => '/cms/people',
                'person' => $person,
                'deleteAction' => '/cms/people/delete/' . $person->id,
                'breadcrumbs' => [
                    [
                        'href' => '/cms/people',
                        'content' => 'People',
                    ],
                ],
            ]
        ));

        return $response;
    }
}
