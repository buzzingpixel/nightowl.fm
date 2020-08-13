<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\MyProfile;

use App\Context\Users\Models\LoggedInUser;
use App\Context\Users\UserApi;
use App\Payload\Payload;
use DateTimeZone;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages as FlashMessages;
use Throwable;

use function count;

class PostMyProfileAction
{
    private LoggedInUser $loggedInUser;
    private FlashMessages $flashMessages;
    private ResponseFactoryInterface $responseFactory;
    private UserApi $userApi;

    public function __construct(
        LoggedInUser $loggedInUser,
        FlashMessages $flashMessages,
        ResponseFactoryInterface $responseFactory,
        UserApi $userApi
    ) {
        $this->loggedInUser    = $loggedInUser;
        $this->flashMessages   = $flashMessages;
        $this->responseFactory = $responseFactory;
        $this->userApi         = $userApi;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->loggedInUser->model();

        $postData = $request->getParsedBody();

        $inputValues = [
            'timezone' => (string) ($postData['timezone'] ?? ''),
        ];

        $inputMessages = [];

        $userTimezone = new DateTimeZone('US/Central');

        if ($inputValues['timezone'] === '') {
            $inputMessages['timezone'] = 'Timezone is required';
        } else {
            try {
                $userTimezone = new DateTimeZone(
                    $inputValues['timezone']
                );
            } catch (Throwable $e) {
                $inputMessages['timezone'] = 'A valid timezone is required';
            }
        }

        if (count($inputMessages) > 0) {
            return $this->respond(
                new Payload(
                    Payload::STATUS_NOT_VALID,
                    [
                        'message' => 'There were errors with your submission',
                        'inputMessages' => $inputMessages,
                        'inputValues' => $inputValues,
                    ],
                ),
            );
        }

        $user->timezone = $userTimezone;

        $payload = $this->userApi->saveUser($user);

        if ($payload->getStatus() !== Payload::STATUS_UPDATED) {
            return $this->respond(
                new Payload(
                    Payload::STATUS_NOT_UPDATED,
                    ['message' => 'An unknown error occurred'],
                ),
            );
        }

        return $this->respond($payload);
    }

    private function respond(Payload $payload): ResponseInterface
    {
        if ($payload->getStatus() !== Payload::STATUS_UPDATED) {
            $this->flashMessages->addMessage(
                'PostMessage',
                [
                    'status' => $payload->getStatus(),
                    'result' => $payload->getResult(),
                ]
            );
        } else {
            $this->flashMessages->addMessage(
                'PostMessage',
                [
                    'status' => Payload::STATUS_SUCCESSFUL,
                    'result' => ['message' => 'Successfully updated your profile'],
                ]
            );
        }

        return $this->responseFactory->createResponse(303)
            ->withHeader(
                'Location',
                '/cms/my-profile'
            );
    }
}
