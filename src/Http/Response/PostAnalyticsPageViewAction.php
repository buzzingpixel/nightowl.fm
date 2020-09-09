<?php

declare(strict_types=1);

namespace App\Http\Response;

use App\Context\Analytics\AnalyticsApi;
use App\Context\Analytics\Models\AnalyticsModel;
use App\Context\Users\UserApi;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use buzzingpixel\cookieapi\interfaces\CookieApiInterface;
use buzzingpixel\cookieapi\interfaces\CookieInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function Safe\json_encode;

class PostAnalyticsPageViewAction
{
    private CookieApiInterface $cookieApi;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private UserApi $userApi;
    private AnalyticsApi $analyticsApi;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        CookieApiInterface $cookieApi,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        UserApi $userApi,
        AnalyticsApi $analyticsApi,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->cookieApi       = $cookieApi;
        $this->uuidFactory     = $uuidFactory;
        $this->userApi         = $userApi;
        $this->analyticsApi    = $analyticsApi;
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(200)
            ->withHeader(
                'Content-type',
                'application/json'
            );

        /** @noinspection PhpUnhandledExceptionInspection */
        $response->getBody()->write(
            json_encode(['status' => 'ok'])
        );

        $user = $this->userApi->fetchLoggedInUser();

        if ($user !== null && $user->isAdmin) {
            return $response;
        }

        $requestData = (array) $request->getParsedBody();

        $analyticsModel = new AnalyticsModel();

        $analyticsModel->cookie = $this->getCookie();

        $analyticsModel->user = $user;

        $analyticsModel->wasLoggedInOnPageLoad = $user !== null;

        $analyticsModel->uri = (string) ($requestData['uri'] ?? '/');

        $this->analyticsApi->createPageView($analyticsModel);

        return $response;
    }

    private function getCookie(): CookieInterface
    {
        $cookie = $this->cookieApi->retrieveCookie('activity_id');

        if ($cookie !== null) {
            return $cookie;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $cookie = $this->cookieApi->makeCookie(
            'activity_id',
            $this->uuidFactory->uuid1()->toString(),
        );

        $this->cookieApi->saveCookie($cookie);

        return $cookie;
    }
}
