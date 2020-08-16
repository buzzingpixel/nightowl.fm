<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\NewShow;

use App\Context\People\PeopleApi;
use App\Context\Shows\ShowConstants;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class NewShowAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private PeopleApi $peopleApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        PeopleApi $peopleApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->peopleApi       = $peopleApi;
    }

    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Create New Show | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twig->render(
            'Http/CMS/Shows/EditShow.twig',
            [
                'meta' => $meta,
                'title' => 'Create New Show',
                'activeNavHref' => '/cms/shows',
                'breadcrumbs' => [
                    [
                        'href' => '/cms/shows',
                        'content' => 'Shows',
                    ],
                ],
                'statusOptions' => ShowConstants::STATUSES_SELECT_ARRAY,
                'peopleOptions' => $this->peopleApi->transformPersonModelsToSelectArray(
                    $this->peopleApi->fetchPeople()
                ),
            ]
        ));

        return $response;
    }
}
