<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Twitter;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class ErrorAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twigEnvironment;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twigEnvironment
    ) {
        $this->responseFactory = $responseFactory;
        $this->twigEnvironment = $twigEnvironment;
    }

    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Error Authorizing Twitter | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twigEnvironment->render(
            'Http/CMS/Twitter/TwitterAuthError.twig',
            [
                'meta' => $meta,
                'title' => 'Authorize Twitter',
                'activeNavHref' => '/cms/twitter',
                'breadcrumbs' => [
                    [
                        'href' => '/cms/twitter',
                        'content' => 'Twitter',
                    ],
                ],
            ]
        ));

        return $response;
    }
}
