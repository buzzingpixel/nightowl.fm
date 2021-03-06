<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Shared;

use App\Context\People\Models\FetchModel as PeopleFetchModel;
use App\Context\People\PeopleApi;
use App\Context\PodcastCategories\Models\FetchModel as PodcastCategoriesFetchModel;
use App\Context\PodcastCategories\PodcastCategoriesApi;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\ShowApi;
use App\Context\Shows\ShowConstants;
use App\Factories\ValidationFactory;
use App\Http\Response\CMS\Shows\EditShow\PostEditShowResponder;
use App\Http\Response\CMS\Shows\NewShow\PostNewShowResponder;
use App\Payload\Payload;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as V;

use function array_merge;
use function assert;
use function count;
use function in_array;
use function is_array;
use function is_string;

class SaveShowFromPost
{
    private ValidationFactory $validationFactory;
    private PeopleApi $peopleApi;
    private ShowApi $showApi;
    private PodcastCategoriesApi $podcastCategoriesApi;

    public function __construct(
        ValidationFactory $validationFactory,
        PeopleApi $peopleApi,
        ShowApi $showApi,
        PodcastCategoriesApi $podcastCategoriesApi
    ) {
        $this->validationFactory    = $validationFactory;
        $this->peopleApi            = $peopleApi;
        $this->showApi              = $showApi;
        $this->podcastCategoriesApi = $podcastCategoriesApi;
    }

    /**
     * @param PostNewShowResponder|PostEditShowResponder $responder
     */
    public function save(
        ServerRequestInterface $request,
        ShowModel $model,
        $responder
    ): ResponseInterface {
        $post = $request->getParsedBody();

        assert(is_array($post));

        $data = [
            'title' => (string) ($post['title'] ?? ''),
            'slug' => (string) ($post['slug'] ?? ''),
            'artwork_file_path' => (string) ($post['artwork']['file_path'] ?? ''),
            'artwork_file_name' => (string) ($post['artwork']['file_name'] ?? ''),
            'description' => (string) ($post['description'] ?? ''),
            'status' => (string) ($post['status'] ?? ''),
            'explicit' => (string) ($post['explicit'] ?? 'false'),
            'itunes_link' => (string) ($post['itunes_link'] ?? ''),
            'google_play_link' => (string) ($post['google_play_link'] ?? ''),
            'stitcher_link' => (string) ($post['stitcher_link'] ?? ''),
            'spotify_link' => (string) ($post['spotify_link'] ?? ''),
            'keywords' => (string) ($post['keywords'] ?? ''),
            'hosts' => (array) ($post['hosts'] ?? []),
            'podcast_categories' => (array) ($post['podcast_categories'] ?? []),
            'patreon_link' => (string) ($post['patreon_link'] ?? ''),
            'patreon_cta' => (string) ($post['patreon_cta'] ?? ''),
            'patreon_headline' => (string) ($post['patreon_headline'] ?? ''),
            'patreon_description' => (string) ($post['patreon_description'] ?? ''),
        ];

        $hosts = [];

        /** @var string[] $hostIds */
        $hostIds = $data['hosts'];

        if (count($hostIds) > 0) {
            $fetchModel      = new PeopleFetchModel();
            $fetchModel->ids = $hostIds;
            $hosts           = $this->peopleApi->fetchPeople(
                $fetchModel
            );
        }

        $validator = $this->validationFactory->make(
            [
                'notEmpty' => 'Value must not be empty',
                'slug' => 'Must not contain spaces, capitalization, or special characters',
            ],
        );

        /** @psalm-suppress MixedArgument */
        $validator->validate(
            $data,
            [
                'title' => V::notEmpty(),
                'slug' => V::allOf(
                    V::notEmpty(),
                    V::slug(),
                    V::callback(
                        /**
                         * @param mixed $input
                         */
                        function ($input) use ($model): bool {
                            if (! is_string($input)) {
                                return false;
                            }

                            return $this->showApi->validateUniqueShowSlug(
                                $input,
                                $model->id,
                            );
                        }
                    )->setTemplate('Show slug must be unique'),
                ),
                'artwork_file_path' => V::callback(
                    /**
                     * @param mixed $input
                     */
                    static function ($input) use ($model): bool {
                        if (! is_string($input)) {
                            return false;
                        }

                        if ($model->id === '') {
                            return $input !== '';
                        }

                        if ($model->artworkFileLocation === '') {
                            return $input !== '';
                        }

                        return true;
                    }
                )->setTemplate('Artwork must be provided'),
                'description' => V::notEmpty(),
                'status' => V::callback(
                    /**
                     * @param mixed $input
                     */
                    static function ($input): bool {
                        if (! is_string($input)) {
                            return false;
                        }

                        return in_array(
                            $input,
                            ShowConstants::STATUSES,
                            true
                        );
                    }
                )->setTemplate('Must be predefined value'),
                'hosts' => V::callback(
                    /**
                     * @param mixed $input
                     */
                    static function ($input) use ($hosts): bool {
                        if (count($hosts) < 1) {
                            return false;
                        }

                        if (! is_array($input)) {
                            return false;
                        }

                        return count($hosts) === count($input);
                    }
                )->setTemplate('Valid hosts must be selected'),
            ]
        );

        if (! $validator->isValid()) {
            $errors = $validator->getErrors();

            if (isset($errors['artwork_file_path'])) {
                $errors['artwork'] = $errors['artwork_file_path'];
            }

            return $responder->respond(
                new Payload(
                    Payload::STATUS_NOT_VALID,
                    [
                        'message' => 'The data provided was invalid',
                        'inputMessages' => $errors,
                        'inputValues' => $post,
                    ]
                ),
                $model,
            );
        }

        $model->title                  = $data['title'];
        $model->slug                   = $data['slug'];
        $model->newArtworkFileLocation = $data['artwork_file_path'];
        $model->description            = $data['description'];
        $model->status                 = $data['status'];
        $model->explicit               = $data['explicit'] === 'true';
        $model->itunesLink             = $data['itunes_link'];
        $model->googlePlayLink         = $data['google_play_link'];
        $model->stitcherLink           = $data['stitcher_link'];
        $model->spotifyLink            = $data['spotify_link'];
        $model->patreonLink            = $data['patreon_link'];
        $model->patreonCta             = $data['patreon_cta'];
        $model->patreonHeadline        = $data['patreon_headline'];
        $model->patreonDescription     = $data['patreon_description'];
        $model->setKeywordsFromCommaString($data['keywords']);
        $model->hosts = $hosts;

        $categories = [];

        /** @var string[] $categoryIds */
        $categoryIds = $data['podcast_categories'];

        if (count($categoryIds) > 0) {
            $fetchModel      = new PodcastCategoriesFetchModel();
            $fetchModel->ids = $categoryIds;
            $categories      = $this->podcastCategoriesApi->fetchCategories(
                $fetchModel
            );
        }

        $model->podcastCategories = $categories;

        $payload = $this->showApi->saveShow($model);

        return $responder->respond(
            new Payload(
                $payload->getStatus(),
                array_merge(
                    $payload->getResult(),
                    ['inputValues' => $post],
                ),
            ),
            $model,
        );
    }
}
