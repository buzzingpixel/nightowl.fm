<?php

declare(strict_types=1);

namespace App\Http\Response\IForgot;

use App\Context\Users\UserApi;
use App\Payload\Payload;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages as FlashMessages;
use Throwable;

use function assert;
use function is_array;

class PostIForgotAction
{
    private UserApi $userApi;
    private ResponseFactoryInterface $responseFactory;
    private FlashMessages $flashMessages;

    public function __construct(
        UserApi $userApi,
        ResponseFactoryInterface $responseFactory,
        FlashMessages $flashMessages
    ) {
        $this->userApi         = $userApi;
        $this->responseFactory = $responseFactory;
        $this->flashMessages   = $flashMessages;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $post = $request->getParsedBody();

        assert(is_array($post));

        $emailAddress = (string) ($post['email_address'] ?? '');

        $user = $this->userApi->fetchUserByEmailAddress(
            $emailAddress
        );

        if ($user === null) {
            return $this->respond();
        }

        if ($this->userApi->fetchTotalUserResetTokens($user) > 5) {
            return $this->respond();
        }

        $this->userApi->requestPasswordResetEmail($user);

        return $this->respond();
    }

    private function respond(): ResponseInterface
    {
        $this->flashMessages->addMessage(
            'LoginFormMessage',
            [
                'status' => Payload::STATUS_SUCCESSFUL,
                'result' => [
                    'message' => 'If that email address is associated with an' .
                        " account and you haven't requested a reset more than 5" .
                        " times in the last 2 hours, we'll send password reset" .
                        ' instructions to that email address.',
                ],
            ]
        );

        return $this->responseFactory->createResponse(303)
            ->withHeader(
                'Location',
                '/iforgot'
            );
    }
}
