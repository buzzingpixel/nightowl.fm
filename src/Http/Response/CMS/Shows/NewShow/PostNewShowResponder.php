<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\NewShow;

use App\Payload\Payload;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Flash\Messages as FlashMessages;

class PostNewShowResponder
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

    public function respond(Payload $payload): ResponseInterface
    {
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
                    '/cms/shows/new',
                );
        }

        $this->flashMessages->addMessage(
            'PostMessage',
            [
                'status' => Payload::STATUS_SUCCESSFUL,
                'result' => ['message' => 'New show created successfully'],
            ]
        );

        return $this->responseFactory->createResponse(303)
            ->withHeader(
                'Location',
                '/cms/shows/edit/' . ((string) $payload->getResult()['id']),
            );
    }
}
