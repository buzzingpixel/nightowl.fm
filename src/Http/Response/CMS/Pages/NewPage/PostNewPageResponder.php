<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Pages\NewPage;

use App\Context\Pages\Models\PageModel;
use App\Payload\Payload;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Flash\Messages as FlashMessages;

class PostNewPageResponder
{
    private FlashMessages $flashMessages;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        FlashMessages $flashMessages,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->flashMessages   = $flashMessages;
        $this->responseFactory = $responseFactory;
    }

    public function respond(
        Payload $payload,
        PageModel $page
    ): ResponseInterface {
        if ($payload->getStatus() !== Payload::STATUS_CREATED) {
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
                    '/cms/pages/new',
                );
        }

        $this->flashMessages->addMessage(
            'PostMessage',
            [
                'status' => Payload::STATUS_SUCCESSFUL,
                'result' => ['message' => 'New page created successfully'],
            ]
        );

        return $this->responseFactory->createResponse(303)
            ->withHeader(
                'Location',
                '/cms/pages/edit/' .
                $page->id,
            );
    }
}
