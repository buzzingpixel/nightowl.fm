<?php

declare(strict_types=1);

namespace App\Http\ServiceSuites\StaticCache;

use App\Http\ServiceSuites\StaticCache\Services\ClearStaticCache;
use App\Http\ServiceSuites\StaticCache\Services\CreateCacheFromResponse;
use App\Http\ServiceSuites\StaticCache\Services\CreateResponseFromCache;
use App\Http\ServiceSuites\StaticCache\Services\DoesCacheFileExistForRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function assert;

class StaticCacheApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function createCacheFromResponse(
        ResponseInterface $response,
        ServerRequestInterface $request
    ): void {
        /** @psalm-suppress MixedAssignment */
        $service = $this->di->get(CreateCacheFromResponse::class);

        assert($service instanceof CreateCacheFromResponse);

        $service($response, $request);
    }

    public function doesCacheFileExistForRequest(
        ServerRequestInterface $request
    ): bool {
        /** @psalm-suppress MixedAssignment */
        $service = $this->di->get(DoesCacheFileExistForRequest::class);

        assert($service instanceof DoesCacheFileExistForRequest);

        return $service($request);
    }

    public function createResponseFromCache(
        ServerRequestInterface $request
    ): ResponseInterface {
        /** @psalm-suppress MixedAssignment */
        $service = $this->di->get(CreateResponseFromCache::class);

        assert($service instanceof CreateResponseFromCache);

        return $service($request);
    }

    public function clearStaticCache(): void
    {
        /** @psalm-suppress MixedAssignment */
        $service = $this->di->get(ClearStaticCache::class);

        assert($service instanceof ClearStaticCache);

        $service();
    }
}
