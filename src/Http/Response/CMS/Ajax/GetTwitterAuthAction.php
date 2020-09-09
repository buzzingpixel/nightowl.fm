<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Ajax;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Context\Twitter\TwitterApi;
use Config\General;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\Exceptions\JsonException;
use Throwable;

use function implode;
use function Safe\json_encode;

class GetTwitterAuthAction
{
    private TwitterApi $twitterApi;
    private ResponseFactoryInterface $responseFactory;
    private General $generalConfig;

    public function __construct(
        TwitterApi $twitterApi,
        ResponseFactoryInterface $responseFactory,
        General $generalConfig
    ) {
        $this->twitterApi      = $twitterApi;
        $this->responseFactory = $responseFactory;
        $this->generalConfig   = $generalConfig;
    }

    /**
     * @throws JsonException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $post = (array) $request->getParsedBody();

        $settings = $this->twitterApi->fetchTwitterSettings();

        $settings->twitterConsumerKey = (string) ($post['twitterConsumerKey'] ?? '');

        $settings->twitterConsumerSecret = (string) ($post['twitterConsumerSecret'] ?? '');

        $this->twitterApi->saveTwitterSettings($settings);

        $response = $this->responseFactory->createResponse()
            ->withHeader(
                'Content-type',
                'application/json',
            );

        try {
            $connection = new TwitterOAuth(
                $settings->twitterConsumerKey,
                $settings->twitterConsumerSecret,
            );

            $token = $connection->oauth(
                'oauth/request_token',
                [
                    'oauth_callback' => implode('/', [
                        $this->generalConfig->siteUrl(),
                        'cms',
                        'twitter',
                        'callback',
                    ]),
                ],
                // https://pipedream.com/sources/dc_6RuXYZ
                // ['oauth_callback' => 'https://d6a48422dd5464bbadc525eb01a0cd56.m.pipedream.net'],
            );

            // Get the URL
            $url = $connection->url('oauth/authenticate', [
                'oauth_token' => $token['oauth_token'],
            ]);

            $response = $response->withStatus(200);

            $response->getBody()->write(json_encode(
                ['url' => $url],
            ));

            return $response;
        } catch (Throwable $e) {
            $response = $response->withStatus(401);

            $response->getBody()->write(json_encode(
                $e->getMessage(),
            ));

            return $response;
        }
    }
}
