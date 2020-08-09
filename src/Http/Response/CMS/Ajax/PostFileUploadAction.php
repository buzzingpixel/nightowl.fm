<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Ajax;

use App\Context\TempFileStorage\TempFileStorageApi;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

use function assert;

class PostFileUploadAction
{
    private ResponseFactoryInterface $responseFactory;
    private TempFileStorageApi $tempFileStorageApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TempFileStorageApi $tempFileStorageApi
    ) {
        $this->responseFactory    = $responseFactory;
        $this->tempFileStorageApi = $tempFileStorageApi;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $file = $request->getUploadedFiles()['file'] ?? null;

        if ($file === null) {
            return $this->responseFactory->createResponse(
                400,
                'File upload not provided',
            );
        }

        assert($file instanceof UploadedFileInterface);

        $response = $this->responseFactory->createResponse()
            ->withHeader(
                'Content-type',
                'application/json',
            );

        $response->getBody()->write(
            $this->tempFileStorageApi->saveUploadedFile(
                $file
            )->toJson(),
        );

        return $response;
    }
}
