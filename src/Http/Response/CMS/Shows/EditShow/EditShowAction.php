<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\EditShow;

use App\Context\People\PeopleApi;
use App\Context\Shows\Models\FetchModel;
use App\Context\Shows\ShowApi;
use App\Context\Shows\ShowConstants;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EditShowAction
{
    private ShowApi $showApi;
    private PeopleApi $peopleApi;
    private ResponseFactoryInterface $responseFactory;
    private Environment $twig;

    public function __construct(
        ShowApi $showApi,
        PeopleApi $peopleApi,
        ResponseFactoryInterface $responseFactory,
        Environment $twig
    ) {
        $this->showApi         = $showApi;
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

        $show = $this->showApi->fetchShow($fetchModel);

        if ($show === null) {
            throw new HttpNotFoundException($request);
        }

        $meta = new Meta();

        $meta->title = 'Edit ' . $show->title . ' | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twig->render(
            'Http/CMS/Shows/EditShow.twig',
            [
                'meta' => $meta,
                'title' => 'Edit ' . $show->title,
                'activeNavHref' => '/cms/shows',
                'show' => $show,
                'deleteAction' => '/cms/shows/delete/' . $show->id,
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
