<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Users\EditUser;

use App\Context\Users\Models\UserModel;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Twig\Environment as TwigEnvironment;

class EditUserResponder
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

    /**
     * @throws Throwable
     */
    public function respond(
        Meta $meta,
        string $pageTitle,
        UserModel $userModel
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twig->render(
            'Http/CMS/Users/EditUser.twig',
            [
                'meta' => $meta,
                'title' => $pageTitle,
                'activeNavHref' => '/cms/users',
                'userModel' => $userModel,
                'deleteAction' => '/cms/users/delete/' . $userModel->id,
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
