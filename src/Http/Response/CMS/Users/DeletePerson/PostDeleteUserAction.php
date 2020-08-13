<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Users\DeletePerson;

use App\Context\Users\UserApi;
use App\Payload\Payload;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Flash\Messages as FlashMessages;

class PostDeleteUserAction
{
    private UserApi $userApi;
    private FlashMessages $flashMessages;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        UserApi $userApi,
        FlashMessages $flashMessages,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->userApi         = $userApi;
        $this->flashMessages   = $flashMessages;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @throws HttpNotFoundException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->userApi->fetchUserById(
            (string) $request->getAttribute('id')
        );

        if ($user === null) {
            throw new HttpNotFoundException($request);
        }

        $payload = $this->userApi->deleteUser($user);

        if ($payload->getStatus() !== Payload::STATUS_SUCCESSFUL) {
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
                    '/cms/users/edit/' . $user->id,
                );
        }

        $this->flashMessages->addMessage(
            'PostMessage',
            [
                'status' => Payload::STATUS_SUCCESSFUL,
                'result' => ['message' => $user->emailAddress . ' deleted successfully'],
            ]
        );

        return $this->responseFactory->createResponse(303)
            ->withHeader(
                'Location',
                '/cms/users',
            );
    }
}
