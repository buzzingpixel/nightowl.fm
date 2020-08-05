<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\NewShow;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class NewShowResponder
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

    public function __invoke(
        Meta $meta,
        string $pageTitle
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twig->render(
            'Http/CMS/Shows/NewShow.twig',
            [
                'meta' => $meta,
                'title' => $pageTitle,
            ]
        ));

        return $response;
    }
}
