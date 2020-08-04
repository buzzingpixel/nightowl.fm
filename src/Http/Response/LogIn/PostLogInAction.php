<?php

declare(strict_types=1);

namespace App\Http\Response\LogIn;

use App\Context\Users\UserApi;
use App\Payload\Payload;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function assert;
use function is_array;

class PostLogInAction
{
    private PostLogInResponder $responder;
    private UserApi $userApi;

    public function __construct(
        PostLogInResponder $responder,
        UserApi $userApi
    ) {
        $this->responder = $responder;
        $this->userApi   = $userApi;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $postData = $request->getParsedBody();

        assert(is_array($postData));

        $emailAddress = (string) ($postData['email_address'] ?? '');

        $password = (string) ($postData['password'] ?? '');

        $redirectTo = (string) ($postData['redirect_to'] ?? '/');

        $user = $this->userApi->fetchUserByEmailAddress(
            $emailAddress
        );

        $errMsg = 'Unable to log you in with that email address and password';

        $errorPayload = new Payload(
            Payload::STATUS_ERROR,
            ['message' => $errMsg]
        );

        if ($user === null) {
            return ($this->responder)(
                $errorPayload,
                $redirectTo
            );
        }

        $logInPayload = $this->userApi->logUserIn(
            $user,
            $password
        );

        if ($logInPayload->getStatus() !== Payload::STATUS_SUCCESSFUL) {
            return ($this->responder)(
                $errorPayload,
                $redirectTo
            );
        }

        return $this->responder->__invoke(
            $logInPayload,
            $redirectTo
        );
    }
}
