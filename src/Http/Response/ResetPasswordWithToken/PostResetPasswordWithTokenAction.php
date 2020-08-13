<?php

declare(strict_types=1);

namespace App\Http\Response\ResetPasswordWithToken;

use App\Context\Users\UserApi;
use App\Payload\Payload;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Flash\Messages as FlashMessages;

use function assert;
use function is_array;

class PostResetPasswordWithTokenAction
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

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $token = (string) $request->getAttribute('token');

        $user = $this->userApi->fetchUserByResetToken($token);

        if ($user === null) {
            throw new HttpNotFoundException($request);
        }

        $postData = $request->getParsedBody();

        assert(is_array($postData));

        $password = (string) ($postData['password'] ?? '');

        $confirmPassword = (string) ($postData['confirm_password'] ?? '');

        if ($password !== $confirmPassword) {
            return $this->respond(
                new Payload(
                    Payload::STATUS_NOT_VALID,
                    [
                        'message' => 'Password confirmation must match password',
                        'inputs' => [
                            'password' => 'Password must match Password Confirmation',
                            'confirmPassword' => 'Password Confirmation must match password',
                        ],
                    ]
                ),
                $token,
            );
        }

        if ($password === '') {
            return $this->respond(
                new Payload(
                    Payload::STATUS_NOT_VALID,
                    [
                        'message' => 'Password cannot be empty',
                        'inputs' => ['password' => 'Password cannot be empty'],
                    ]
                ),
                $token,
            );
        }

        $savePayload = $this->userApi->resetPasswordByToken(
            $token,
            $password
        );

        return $this->respond(
            new Payload(
                $savePayload->getStatus(),
                [
                    'message' => $savePayload->getResult()['message'] ?? '',
                    'inputs' => $savePayload->getResult(),
                ]
            ),
            $token,
        );
    }

    public function respond(Payload $payload, string $token): ResponseInterface
    {
        if ($payload->getStatus() !== Payload::STATUS_UPDATED) {
            $this->flashMessages->addMessage(
                'LoginFormMessage',
                [
                    'status' => $payload->getStatus(),
                    'result' => $payload->getResult(),
                ]
            );

            return $this->responseFactory->createResponse(303)
                ->withHeader(
                    'Location',
                    '/reset-pw-with-token/' . $token,
                );
        }

        $this->flashMessages->addMessage(
            'LoginFormMessage',
            [
                'status' => Payload::STATUS_SUCCESSFUL,
                'result' => [
                    'message' => 'Your password was updated successfully.' .
                        ' You can now log in with your new password.',
                ],
            ]
        );

        return $this->responseFactory->createResponse(303)
            ->withHeader(
                'Location',
                '/cms',
            );
    }
}
