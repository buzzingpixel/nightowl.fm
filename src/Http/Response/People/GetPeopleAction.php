<?php

declare(strict_types=1);

namespace App\Http\Response\People;

use App\Context\People\PeopleApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GetPeopleAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private PeopleApi $peopleApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        PeopleApi $peopleApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->peopleApi       = $peopleApi;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        // $pageNum = 1;

        // $pageNumAttr = $request->getAttribute('pageNum');

        // if ($pageNumAttr !== null) {
        //     $pageNum = (int) $pageNumAttr;
        //
        //     if ($pageNum < 2) {
        //         throw new HttpNotFoundException($request);
        //     }
        // }

        $response = $this->responseFactory->createResponse();

        $meta = new Meta();

        $meta->title = 'People';

        $response->getBody()->write(
            $this->twig->render(
                'Http/People.twig',
                [
                    'meta' => $meta,
                    // 'hosts' => $pageNum < 2 ?
                    //     $this->peopleApi->getHosts() :
                    //     [],
                    'hosts' => $this->peopleApi->getHosts(),
                    'guests' => $this->peopleApi->getGuests(),
                ]
            ),
        );

        return $response;
    }
}
