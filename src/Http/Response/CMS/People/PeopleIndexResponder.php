<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People;

use App\Context\People\Models\PersonModel;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class PeopleIndexResponder
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
     * @param PersonModel[] $people
     */
    public function respond(
        Meta $meta,
        string $pageTitle,
        array $people
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/People/Index.twig',
                [
                    'meta' => $meta,
                    'title' => $pageTitle,
                    'activeNavHref' => '/cms/people',
                    'people' => $people,
                ],
            ),
        );

        return $response;
    }
}
