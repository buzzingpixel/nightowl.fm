<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Users;

use App\Context\Users\UserApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Twig\Environment as TwigEnvironment;

class UsersIndexAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private UserApi $userApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        UserApi $userApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->userApi         = $userApi;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Users | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/Users/Index.twig',
                [
                    'meta' => $meta,
                    'title' => 'CMS Users',
                    'activeNavHref' => '/cms/users',
                    'users' => $this->userApi->fetchUsersByLimitOffset(),
                ],
            ),
        );

        return $response;
    }
}
