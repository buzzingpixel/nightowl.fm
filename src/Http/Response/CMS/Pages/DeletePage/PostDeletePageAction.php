<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Pages\DeletePage;

use App\Context\Pages\Models\FetchModel;
use App\Context\Pages\PagesApi;
use App\Payload\Payload;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Flash\Messages as FlashMessages;

class PostDeletePageAction
{
    private PagesApi $pagesApi;
    private FlashMessages $flashMessages;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        PagesApi $pagesApi,
        FlashMessages $flashMessages,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->pagesApi        = $pagesApi;
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

        $page = $this->pagesApi->fetchPage($fetchModel);

        if ($page === null) {
            throw new HttpNotFoundException($request);
        }

        $payload = $this->pagesApi->deletePage($page);

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
                    '/cms/pages/edit/' . $page->id,
                );
        }

        $this->flashMessages->addMessage(
            'PostMessage',
            [
                'status' => Payload::STATUS_SUCCESSFUL,
                'result' => ['message' => $page->title . ' deleted successfully'],
            ]
        );

        return $this->responseFactory->createResponse(303)
            ->withHeader(
                'Location',
                '/cms/pages',
            );
    }
}
