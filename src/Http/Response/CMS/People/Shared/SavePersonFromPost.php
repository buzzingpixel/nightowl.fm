<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People\Shared;

use App\Context\Links\Models\LinkModel;
use App\Context\People\Models\PersonModel;
use App\Context\People\PeopleApi;
use App\Factories\ValidationFactory;
use App\Http\Response\CMS\People\EditPerson\PostEditPersonResponder;
use App\Http\Response\CMS\People\NewPerson\PostNewPersonResponder;
use App\Payload\Payload;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as V;

use function assert;
use function filter_var;
use function is_array;
use function is_string;

use const FILTER_VALIDATE_EMAIL;

class SavePersonFromPost
{
    private ValidationFactory $validationFactory;
    private PeopleApi $peopleApi;

    public function __construct(
        ValidationFactory $validationFactory,
        PeopleApi $peopleApi
    ) {
        $this->validationFactory = $validationFactory;
        $this->peopleApi         = $peopleApi;
    }

    /**
     * @param PostNewPersonResponder|PostEditPersonResponder $responder
     */
    public function save(
        ServerRequestInterface $request,
        PersonModel $person,
        $responder
    ): ResponseInterface {
        $post = $request->getParsedBody();

        assert(is_array($post));

        $data = [
            'first_name' => (string) ($post['first_name'] ?? ''),
            'last_name' => (string) ($post['last_name'] ?? ''),
            'slug' => (string) ($post['slug'] ?? ''),
            'email' => (string) ($post['email'] ?? ''),
            'photo_file_path' => (string) ($post['photo']['file_path'] ?? ''),
            'photo_file_name' => (string) ($post['photo']['file_name'] ?? ''),
            'photo_preference' => (string) ($post['photo_preference'] ?? ''),
            'bio' => (string) ($post['bio'] ?? ''),
            'location' => (string) ($post['location'] ?? ''),
            'facebook_page_slug' => (string) ($post['facebook_page_slug'] ?? ''),
            'twitter_handle' => (string) ($post['twitter_handle'] ?? ''),
            'links' => [],
        ];

        $validator = $this->validationFactory->make(
            [
                'notEmpty' => 'Value must not be empty',
                'email' => 'Must be a valid email address',
                'slug' => 'Must not contain spaces, capitalization, or special characters',
            ],
        );

        /** @psalm-suppress MixedArgument */
        $validator->validate(
            $data,
            [
                'first_name' => V::notEmpty(),
                'last_name' => V::notEmpty(),
                'slug' => V::allOf(
                    V::notEmpty(),
                    V::slug(),
                    V::callback(
                        /**
                         * @param mixed $input
                         */
                        function ($input) use ($person): bool {
                            if (! is_string($input)) {
                                return false;
                            }

                            return $this->peopleApi->validateUnqiePersonSlug(
                                $input,
                                $person->id,
                            );
                        }
                    )->setTemplate('Person slug must be unique'),
                ),
                'email' => V::callback(
                    /**
                     * @param mixed $input
                     */
                    static function ($input): bool {
                        if (! is_string($input)) {
                            return false;
                        }

                        if ($input === '') {
                            return true;
                        }

                        $valid = filter_var(
                            $input,
                            FILTER_VALIDATE_EMAIL
                        );

                        return $valid !== false;
                    },
                )->setTemplate('Must be a valid email address'),
                'photo_preference' => V::notEmpty(),
                'bio' => V::notEmpty(),
            ]
        );

        if (! $validator->isValid()) {
            return $responder->respond(
                new Payload(
                    Payload::STATUS_NOT_VALID,
                    [
                        'message' => 'The data provided was invalid',
                        'inputMessages' => $validator->getErrors(),
                        'inputValues' => $post,
                    ]
                ),
                $person,
            );
        }

        /** @psalm-suppress MixedAssignment */
        foreach (($post['links']['link_title'] ?? []) as $key => $title) {
            $title = (string) $title;
            /** @psalm-suppress MixedArrayOffset */
            $url = (string) ($post['links']['link_url'][$key] ?? '');

            if ($title === '' || $url === '') {
                continue;
            }

            $data['links'][] = new LinkModel(
                $title,
                $url,
            );
        }

        $person->firstName            = $data['first_name'];
        $person->lastName             = $data['last_name'];
        $person->slug                 = $data['slug'];
        $person->email                = $data['email'];
        $person->newPhotoFileLocation = $data['photo_file_path'];
        $person->photoPreference      = $data['photo_preference'];
        $person->bio                  = $data['bio'];
        $person->location             = $data['location'];
        $person->facebookPageSlug     = $data['facebook_page_slug'];
        $person->twitterHandle        = $data['twitter_handle'];
        $person->setLinks($data['links']);

        return $responder->respond(
            $this->peopleApi->savePerson($person),
            $person,
        );
    }
}
