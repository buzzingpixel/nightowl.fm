<?php

declare(strict_types=1);

namespace App\Http\Response\Show;

use App\Context\Episodes\Models\EpisodeModel;
use DaveRandom\Resume\FileResource;
use DaveRandom\Resume\InvalidRangeHeaderException;
use DaveRandom\Resume\NonExistentFileException;
use DaveRandom\Resume\RangeSet;
use DaveRandom\Resume\ResourceServlet;
use DaveRandom\Resume\SendFileFailureException;
use DaveRandom\Resume\UnreadableFileException;
use DaveRandom\Resume\UnsatisfiableRangeException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetEpisodeDownloadAction
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        ResponseFactoryInterface $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    public function get(
        ServerRequestInterface $request,
        EpisodeModel $episode
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse();

        try {
            $params = $request->getServerParams();

            $rangeHeader = (string) ($params['HTTP_RANGE'] ?? '');

            $rangeSet = null;

            if ($rangeHeader !== '') {
                $rangeSet = RangeSet::createFromHeader($rangeHeader);
            }

            $resource = new FileResource(
                $episode->getFullFilePath(),
                $episode->fileMimeType,
            );

            $servlet = new ResourceServlet($resource);

            $servlet->sendResource($rangeSet);

            exit;
        } catch (InvalidRangeHeaderException $e) {
            $response = $response->withStatus(
                400,
                'Bad Request'
            );
        } catch (UnsatisfiableRangeException $e) {
            $response = $response->withStatus(
                416,
                'Range Not Satisfiable'
            );
        } catch (NonExistentFileException $e) {
            $response = $response->withStatus(
                404,
                'Not Found'
            );
        } catch (UnreadableFileException $e) {
            $response = $response->withStatus(
                500,
                'Internal Server Error'
            );
        } catch (SendFileFailureException $e) {
            $response = $response->withStatus(
                500,
                'Internal Server Error'
            );
        }

        return $response;
    }
}
