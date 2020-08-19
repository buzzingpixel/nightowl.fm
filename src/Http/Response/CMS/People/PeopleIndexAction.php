<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People;

use App\Context\People\PeopleApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class PeopleIndexAction
{
    private PeopleApi $peopleApi;
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;

    public function __construct(
        PeopleApi $peopleApi,
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig
    ) {
        $this->peopleApi       = $peopleApi;
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
    }

    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'People | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/People/Index.twig',
                [
                    'meta' => $meta,
                    'title' => 'People',
                    'activeNavHref' => '/cms/people',
                    'people' => $this->peopleApi->fetchPeople(),
                ],
            ),
        );

        return $response;
    }
}
