<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Ajax;

use App\Context\FileManager\FileManagerApi;
use App\Payload\Payload;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Safe\Exceptions\JsonException;

use function assert;
use function Safe\json_encode;

class PostFileManagerUploadAction
{
    private ResponseFactoryInterface $responseFactory;
    private FileManagerApi $fileManagerApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        FileManagerApi $fileManagerApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->fileManagerApi  = $fileManagerApi;
    }

    /**
     * @throws JsonException
     */
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

        $payload = $this->fileManagerApi->saveUploadedFile($file);

        $statusCode = $payload->getStatus() === Payload::STATUS_SUCCESSFUL ?
            200 :
            400;

        $response = $this->responseFactory->createResponse(
            $statusCode,
            $payload->getResult()['message'] ?? '',
        )
            ->withHeader(
                'Content-type',
                'application/json',
            );

        $response->getBody()->write(
            json_encode($payload->getResult())
        );

        return $response;
    }
}
