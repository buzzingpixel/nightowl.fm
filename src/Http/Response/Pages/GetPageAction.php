<?php

declare(strict_types=1);

namespace App\Http\Response\Pages;

use App\Context\Pages\Models\PageModel;
use App\Http\Models\Meta;
use cebe\markdown\GithubMarkdown;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class GetPageAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private GithubMarkdown $markdown;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        GithubMarkdown $markdown
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->markdown        = $markdown;
    }

    public function get(PageModel $page): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();

        $meta = new Meta();

        $meta->title = $page->title;

        $template = $this->twig->createTemplate($page->content);

        $response->getBody()->write(
            $this->twig->render(
                'Http/Page.twig',
                [
                    'meta' => $meta,
                    'title' => $page->title,
                    'content' => $this->markdown->parse(
                        $template->render()
                    ),
                ],
            ),
        );

        return $response;
    }
}
