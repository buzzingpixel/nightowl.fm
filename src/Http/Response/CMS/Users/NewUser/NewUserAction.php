<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Users\NewUser;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class NewUserAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
    }

    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Create New Person | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twig->render(
            'Http/CMS/Users/EditUser.twig',
            [
                'meta' => $meta,
                'title' => 'Create New User',
                'activeNavHref' => '/cms/users',
                'breadcrumbs' => [
                    [
                        'href' => '/cms/users',
                        'content' => 'CMS Users',
                    ],
                ],
            ]
        ));

        return $response;
    }
}
