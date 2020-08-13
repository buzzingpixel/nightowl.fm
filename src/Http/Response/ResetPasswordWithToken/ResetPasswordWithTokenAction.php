<?php

declare(strict_types=1);

namespace App\Http\Response\ResetPasswordWithToken;

use App\Context\Users\UserApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Throwable;
use Twig\Environment as TwigEnvironment;

class ResetPasswordWithTokenAction
{
    private UserApi $userApi;
    private TwigEnvironment $twigEnvironment;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        UserApi $userApi,
        TwigEnvironment $twigEnvironment,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->userApi         = $userApi;
        $this->twigEnvironment = $twigEnvironment;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @throws Throwable
     * @throws HttpNotFoundException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $token = (string) $request->getAttribute('token');

        $user = $this->userApi->fetchUserByResetToken($token);

        if ($user === null) {
            throw new HttpNotFoundException($request);
        }

        $meta = new Meta();

        $meta->title = 'Reset Your Password';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twigEnvironment->render(
            'Http/ResetPassword.twig',
            [
                'meta' => $meta,
                'user' => $user,
            ]
        ));

        return $response;
    }
}
