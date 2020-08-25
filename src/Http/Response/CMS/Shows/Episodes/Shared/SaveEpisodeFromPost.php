<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Episodes\Shared;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\People\Models\FetchModel as PeopleFetchModel;
use App\Context\People\PeopleApi;
use App\Context\Series\Models\FetchModel as SeriesFetchModel;
use App\Context\Series\SeriesApi;
use App\Context\Shows\Models\ShowModel;
use App\Context\Users\Models\LoggedInUser;
use App\Factories\ValidationFactory;
use App\Http\Response\CMS\Shows\Episodes\EditEpisode\PostEditEpisodeResponder;
use App\Http\Response\CMS\Shows\Episodes\NewEpisode\PostNewEpisodeResponder;
use App\Payload\Payload;
use App\Utilities\ValidatePlayableMimeType;
use DateTimeImmutable;
use DateTimeZone;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as V;

use function array_merge;
use function assert;
use function count;
use function in_array;
use function is_array;

class SaveEpisodeFromPost
{
    private ValidationFactory $validationFactory;
    private PeopleApi $peopleApi;
    private SeriesApi $seriesApi;
    private LoggedInUser $user;
    private EpisodeApi $episodeApi;
    private ValidatePlayableMimeType $validatePlayableMimeType;

    public function __construct(
        ValidationFactory $validationFactory,
        PeopleApi $peopleApi,
        SeriesApi $seriesApi,
        LoggedInUser $user,
        EpisodeApi $episodeApi,
        ValidatePlayableMimeType $validatePlayableMimeType
    ) {
        $this->validationFactory        = $validationFactory;
        $this->peopleApi                = $peopleApi;
        $this->seriesApi                = $seriesApi;
        $this->user                     = $user;
        $this->episodeApi               = $episodeApi;
        $this->validatePlayableMimeType = $validatePlayableMimeType;
    }

    /**
     * @param PostNewEpisodeResponder|PostEditEpisodeResponder $responder
     */
    public function save(
        ServerRequestInterface $request,
        EpisodeModel $episode,
        $responder,
        ShowModel $show
    ): ResponseInterface {
        $post = $request->getParsedBody();

        assert(is_array($post));

        $data = [
            'title' => (string) ($post['title'] ?? $episode->title),
            'status' => (string) ($post['status'] ?? $episode->status),
            'description' => (string) ($post['description'] ?? $episode->description),
            'file_path' => (string) ($post['file']['file_path'] ?? ''),
            'file_name' => (string) ($post['file']['file_name'] ?? ''),
            'type' => (string) ($post['type'] ?? $episode->episodeType),
            'explicit' => (string) ($post['explicit'] ?? ($episode->explicit ? 'true' : 'false')),
            'show_notes' => (string) ($post['show_notes'] ?? $episode->showNotes),
            'hosts' => (array) ($post['hosts'] ?? []),
            'guests' => (array) ($post['guests'] ?? []),
            'keywords' => (string) ($post['keywords'] ?? ''),
            'series' => (array) ($post['series'] ?? []),
            'publish_at' => (string) ($post['publish_at'] ?? 'false'),
        ];

        if ($data['publish_at'] === 'false' && $episode->publishAt) {
            $data['publish_at'] = $episode->publishAt
                ->setTimezone($this->user->model()->timezone)
                ->format('Y-m-d g:i A');
        } elseif ($data['publish_at'] === 'false') {
            $data['publish_at'] = '';
        }

        $hosts = [];

        if (count($data['hosts']) > 0) {
            $peopleFetchModel      = new PeopleFetchModel();
            $peopleFetchModel->ids = $data['hosts'];
            $hosts                 = $this->peopleApi->fetchPeople(
                $peopleFetchModel
            );
        }

        $validationErrors = $this->getValidationErrors(
            $data,
            $hosts,
            $episode
        );

        if (count($validationErrors) > 0) {
            if (isset($validationErrors['file_path'])) {
                $validationErrors['file'] = $validationErrors['file_path'];
            }

            return $responder->respond(
                new Payload(
                    Payload::STATUS_NOT_VALID,
                    [
                        'message' => 'The data provided was invalid',
                        'inputMessages' => $validationErrors,
                        'inputValues' => $post,
                    ]
                ),
                $episode,
                $show,
            );
        }

        $guests = [];

        if (count($data['guests']) > 0) {
            $peopleFetchModel      = new PeopleFetchModel();
            $peopleFetchModel->ids = $data['guests'];
            $guests                = $this->peopleApi->fetchPeople(
                $peopleFetchModel
            );
        }

        $series = [];

        if (count($data['series']) > 0) {
            $seriesFetchModel      = new SeriesFetchModel();
            $seriesFetchModel->ids = $data['series'];
            $series                = $this->seriesApi->fetchSeries(
                $seriesFetchModel
            );
        }

        $episode->show            = $show;
        $episode->title           = $data['title'];
        $episode->status          = $data['status'];
        $episode->description     = $data['description'];
        $episode->newFileLocation = $data['file_path'];
        $episode->episodeType     = $data['type'];
        $episode->explicit        = $data['explicit'] === 'true';
        $episode->showNotes       = $data['show_notes'];
        $episode->hosts           = $hosts;
        $episode->guests          = $guests;
        $episode->series          = $series;
        $episode->setKeywordsFromCommaString($data['keywords']);

        if ($data['publish_at'] !== '') {
            $publishAt = \Safe\DateTimeImmutable::createFromFormat(
                'Y-m-d g:i A',
                $data['publish_at'],
                $this->user->model()->timezone
            )->setTimezone(new DateTimeZone('UTC'));

            assert($publishAt instanceof DateTimeImmutable);

            $episode->publishAt = $publishAt;
        }

        $payload = $this->episodeApi->saveEpisode($episode);

        return $responder->respond(
            new Payload(
                $payload->getStatus(),
                array_merge(
                    $payload->getResult(),
                    ['inputValues' => $post],
                )
            ),
            $episode,
            $show,
        );
    }

    /**
     * @param mixed[] $data
     * @param mixed[] $hosts
     *
     * @return mixed[]
     */
    private function getValidationErrors(
        array $data,
        array $hosts,
        EpisodeModel $episode
    ): array {
        if ($data['status'] === EpisodeConstants::EPISODE_STATUS_DRAFT) {
            return [];
        }

        $validator = $this->validationFactory->make(
            ['notEmpty' => 'Value must not be empty'],
        );

        $validator->validate(
            $data,
            [
                'title' => V::notEmpty(),
                'status' => V::callback(
                    static function ($input): bool {
                        return in_array(
                            $input,
                            EpisodeConstants::STATUSES
                        );
                    }
                )->setTemplate('Must be predefined value'),
                'description' => V::notEmpty(),
                'file_path' => V::allOf(
                    V::callback(
                        static function ($input) use ($episode): bool {
                            if ($episode->id === '') {
                                return $input !== '';
                            }

                            if ($episode->fileLocation === '') {
                                return $input !== '';
                            }

                            return true;
                        }
                    )->setTemplate('File must be provided'),
                    V::callback(
                        function ($input): bool {
                            if ($input === '') {
                                return true;
                            }

                            return $this->validatePlayableMimeType->fromTempPath(
                                $input
                            );
                        }
                    )->setTemplate('File must be an MP3')
                ),
                'type' => V::callback(
                    static function ($input): bool {
                        return in_array(
                            $input,
                            EpisodeConstants::TYPES
                        );
                    }
                )->setTemplate('Must be predefined value'),
                'hosts' => V::callback(
                    static function ($input) use ($hosts): bool {
                        if (count($hosts) < 1) {
                            return false;
                        }

                        return count($hosts) === count($input);
                    }
                )->setTemplate('Valid hosts must be selected'),
            ]
        );

        return $validator->getErrors();
    }
}
