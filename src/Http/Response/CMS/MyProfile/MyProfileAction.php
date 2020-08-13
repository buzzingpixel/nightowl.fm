<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\MyProfile;

use App\Context\Users\Models\LoggedInUser;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class MyProfileAction
{
    private LoggedInUser $loggedInUser;
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twigEnvironment;

    public function __construct(
        LoggedInUser $loggedInUser,
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twigEnvironment
    ) {
        $this->loggedInUser    = $loggedInUser;
        $this->responseFactory = $responseFactory;
        $this->twigEnvironment = $twigEnvironment;
    }

    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Edit Your Profile';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twigEnvironment->render(
            'Http/CMS/MyProfile/MyProfile.twig',
            [
                'meta' => $meta,
                'title' => 'Edit Your Profile',
                'activeNavHref' => '',
                'user' => $this->loggedInUser->model(),
            ]
        ));

        return $response;
    }
}
