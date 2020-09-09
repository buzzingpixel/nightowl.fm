<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use App\Context\Twitter\TwitterApi;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TwitterOauthCallbackAction
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

    /**
     * @throws TwitterOAuthException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();

        $settings = $this->twitterApi->fetchTwitterSettings();

        $connection = new TwitterOAuth(
            $settings->twitterConsumerKey,
            $settings->twitterConsumerSecret,
        );

        $accessToken = $connection->oauth(
            'oauth/access_token',
            [
                'oauth_token' => $query['oauth_token'],
                'oauth_verifier' => $query['oauth_verifier'],
            ]
        );

        $settings->twitterAuth       = true;
        $settings->twitterOathToken  = (string) $accessToken['oauth_token'];
        $settings->twitterOathSecret = (string) $accessToken['oauth_token_secret'];
        $settings->twitterUserId     = (string) $accessToken['user_id'];
        $settings->twitterScreenName = (string) $accessToken['screen_name'];

        $this->twitterApi->saveTwitterSettings($settings);

        return $this->responseFactory->createResponse(303)
            ->withHeader('Location', '/cms/twitter');
    }
}
