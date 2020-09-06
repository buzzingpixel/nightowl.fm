<?php

declare(strict_types=1);

namespace App\Http\Response\Show;

use App\Context\EpisodeDownloadStats\EpisodeDownloadStatsApi;
use App\Context\EpisodeDownloadStats\LibraryImplementations\ResourceServlet;
use App\Context\EpisodeDownloadStats\Models\EpisodeDownloadTrackerModel;
use App\Context\Episodes\Models\EpisodeModel;
use DaveRandom\Resume\FileResource;
use DaveRandom\Resume\InvalidRangeHeaderException;
use DaveRandom\Resume\NonExistentFileException;
use DaveRandom\Resume\RangeSet;
use DaveRandom\Resume\SendFileFailureException;
use DaveRandom\Resume\UnreadableFileException;
use DaveRandom\Resume\UnsatisfiableRangeException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function mb_strtolower;

class GetEpisodeDownloadAction
{
    private ResponseFactoryInterface $responseFactory;
    private EpisodeDownloadStatsApi $episodeDownloadStatsApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        EpisodeDownloadStatsApi $episodeDownloadStatsApi
    ) {
        $this->responseFactory         = $responseFactory;
        $this->episodeDownloadStatsApi = $episodeDownloadStatsApi;
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

            $this->saveToTracker(
                $rangeSet,
                $episode,
                $request
            );

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

    private function saveToTracker(
        ?RangeSet $rangeSet,
        EpisodeModel $episode,
        ServerRequestInterface $request
    ): void {
        if (mb_strtolower($request->getMethod()) === 'head') {
            return;
        }

        $bytes = (int) $episode->fileSizeBytes;

        $bytesZero = $bytes - 1;

        if ($rangeSet === null) {
            $model = new EpisodeDownloadTrackerModel();

            $model->episode = $episode;

            $model->isFullRange = true;

            $model->rangeStart = 0;

            $model->rangeEnd = $bytesZero;

            $this->episodeDownloadStatsApi->saveTracker($model);

            return;
        }

        $ranges = $rangeSet->getRangesForSize($bytes);

        foreach ($ranges as $range) {
            $model = new EpisodeDownloadTrackerModel();

            $model->episode = $episode;

            if (
                $range->getStart() === 0 &&
                ($range->getEnd() === null || $range->getEnd() >= $bytesZero)
            ) {
                $model->isFullRange = true;

                $model->rangeStart = 0;

                $model->rangeEnd = $bytesZero;

                $this->episodeDownloadStatsApi->saveTracker($model);

                continue;
            }

            $model->rangeStart = $range->getStart();

            $model->rangeEnd = $range->getEnd() ?? $bytesZero;

            $this->episodeDownloadStatsApi->saveTracker($model);
        }
    }
}
