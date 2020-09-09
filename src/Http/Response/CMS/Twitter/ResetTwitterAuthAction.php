<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Twitter;

use App\Context\Twitter\Models\TwitterSettingsModel;
use App\Context\Twitter\TwitterApi;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResetTwitterAuthAction
{
    private TwitterApi $twitterApi;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        TwitterApi $twitterApi,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->twitterApi      = $twitterApi;
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $oldSettings = $this->twitterApi->fetchTwitterSettings();

        $settings = new TwitterSettingsModel();

        $settings->settingId = $oldSettings->settingId;

        $this->twitterApi->saveTwitterSettings($settings);

        return $this->responseFactory->createResponse(303)
            ->withHeader('Location', '/cms/twitter');
    }
}
