<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People\EditPerson;

use App\Context\People\Models\PersonModel;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class EditPersonResponder
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
    }

    public function respond(
        Meta $meta,
        string $pageTitle,
        PersonModel $person
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twig->render(
            'Http/CMS/People/EditPerson.twig',
            [
                'meta' => $meta,
                'title' => $pageTitle,
                'activeNavHref' => '/cms/people',
                'person' => $person,
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
